<?php

/**
 * ProjectController
 * @since 1.1
 * @author f0t0n
 * @author Bogomazov Bogdan <b.bogomazov@gmail.com> (changes)
 */
class ProjectController extends Controller {

    /**
     * @var string Layout
     */
    public $layout = '//layouts/column2';

    // CListView->pager->pageSize
    const PAGE_SIZE = 30;
    // Count of project on page
    const PROJECTS_ON_PAGE = 20;

    /**
     * 
     * @return type
     */
    public function actions() {
        $actions = array(
            'create' => 'CreateAction',
            'delete' => 'DeleteAction',
            'edit' => 'EditAction',
            'manage' => 'ManageAction',
            'people' => 'PeopleAction',
        );
        $addPrefix = function($item) {
            return 'application.controllers.project.' . $item;
        };
        return array_map($addPrefix, $actions);
    }

    /**
     * 
     */
    public function actionChoose() {
        $redirectUrl = $this->request->getParam('rr');
        $project_id = $this->request->getParam('menu_project_id');
        Yii::app()->user->setState('clearCompanyCache', 1);
        if (empty($project_id)) {
            Project::setCurrent(null);
            $this->redirect($this->createUrl('/project'));
        }
        $dependency = new CDbCacheDependency(
                'SELECT name FROM {{project}} WHERE project_id=:project_id'
        );
        $dependency->params = array(':project_id' => $project_id);
        $project = Project::model()
                ->cache(300, $dependency)
                ->findByPk($project_id);
        //clear filters after switching to new project
        BugFilter::emptyFilterState();
        if (empty($project))
            $this->redirect($this->createUrl('/project'));
        if (!$project->isCompanyAccessAllowed())
            $this->redirect($this->createUrl('/project'));
        Project::setCurrent($project);
        if (empty($redirectUrl))
            $redirectUrl = 'bug/';
        $this->redirect($this->createUrl($redirectUrl));
    }

    /**
     * List of project
     * @param int|bool $archived Show archived projects
     */
    public function actionIndex($archived = 0) {
        if (Yii::app()->user->isGuest) {
            $this->redirect($this->createUrl('site/login'));
        }

        $this->showHelpForNewUsers();
        $user = User::current();

        // Simple register ClientScript files
        $this->registerSimplyClientScriptFiles(array(
            '/js/plug-in/jquery-json/jquery.json.min.js' => 'css',
            '/js/plug-in/fprogress-bar/fprogress-bar.css' => 'css',
            '/js/plug-in/jquery-gantt/js/jquery.fn.gantt.min.js' => 'script',
            '/js/plug-in/jquery-gantt/style.css' => 'css',
            '/js/plug-in/jquery-form/jquery.form.min.js' => 'script',
            '/js/project/index/common.min.js' => 'script'), Yii::app()->baseUrl);

        // Project
        $project = new Project('search');
        $attributes = $this->request->getParam('Project');
        if (!empty($attributes)) {
            $project->setAttributes($attributes);
        }

        // Build view data
        $viewData = array(
            'companies' => array(),
            'formAction' => $this->createUrl('project/create'),
            'projectForm' => new ProjectForm(),
            'project' => $archived ? $project->archived()->visibleOnly() : $project->active()->visibleOnly(),
            'projectSettings' => new SettingsByProject(),
            'pager' => array('pageSize' => self::PAGE_SIZE, 'header' => false),
            'pagination' => array('pageSize' => self::PROJECTS_ON_PAGE),
            'ajax' => FALSE
        );

        $companies = empty($user->company) ? array() : $user->company;
        foreach ($companies as $company) {
            $viewData['companies'][$company->company_id] = $company->company_name;
        }

        // MixPanel events tracking
        MixPanel::instance()->registerEvent(MixPanel::PROJECTS_PAGE_VIEW);

        // Check request & render
        if ($this->request->isAjaxRequest) {
            $viewData['ajax'] = TRUE;
            echo $this->renderPartial('index', $viewData);
        } else {
            $this->render('index', $viewData);
        }
    }

    /**
     * 
     * @param type $id
     */
    public function actionHide($id) {
        $user = User::current();
        $project = Project::model()->findByPk((int) $id);
        UserByProject::model()->deleteAllByAttributes(array(
            'user_id' => $user->user_id,
            'project_id' => $project->project_id,
        ));
        $this->redirect('/project');
    }

    /**
     * 
     */
    public function actionUploadLogo() {
        if (!Yii::app()->request->isAjaxRequest)
            Yii::app()->end();
        $user = User::current();
        if (empty($user))
            Yii::app()->end();
        Yii::import('ext.EAjaxUpload.qqFileUploader');
        $folder = Yii::getPathOfAlias('webroot.temp.project_logo') . '/'; // folder for uploaded files
        $allowedExtensions = array('jpg', 'jpeg', 'gif', 'png');
        $sizeLimit = 2097152; // 2 MB - the maximum file size
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload($folder);
        if (!empty($result['success'])) {
            $tmpFile = new TmpFile('insert');
            $tmpFile->path = '/project_logo/' . $result['filename'];
            if ($tmpFile->save())
                $result['tmpFileID'] = $tmpFile->id;
            else {
                $result['errors'] = $tmpFile->getErrors();
                $result['tmpFileID'] = '0';
            }
        }
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        Yii::app()->end();
    }

    /**
     * 
     * @param type $data
     * @return type
     */
    protected function getHomePageHtml($data) {
        $url = $data->home_page;
        if (!empty($url)) {
            $url = trim($data->home_page, ' /');
            if (!preg_match('@\s*?https?://.+?\s*?$@', $data->home_page) > 0)
                $url = 'http://' . $url;
        }
        return empty($url) ? '<img alt="" src="'
                . Yii::app()->theme->baseUrl . '/images/icons/none16.png" />' : CHtml::link(
                        '<img alt="" src="'
                        . Yii::app()->theme->baseUrl . '/images/icons/html.png" />', $url, array('target' => '_blank')
        );
    }

    /**
     * 
     * @param type $data
     * @return type
     */
    protected function getSwitchProjectBtn($data) {
        $url = $this->createUrl(
                '/project/choose', array('rr' => '', 'menu_project_id' => $data->project_id)
        );
        return CHtml::link(
                        'Switch to', $url, array('class' => 'bkButtonBlueSmall')
        );
    }

    /**
     * 
     * @param type $projectID
     * @return type
     */
    protected function getTasksCounts($projectID) {
        $sql = 'SELECT totalScalar.total, completedScalar.completed FROM
                (
                    SELECT COUNT(*) AS total FROM {{bug}} WHERE 1
                    AND project_id=:project_id
                ) AS totalScalar,
                (
                    SELECT COUNT(*) AS completed FROM {{bug}} WHERE 1
                    AND project_id=:project_id
                    AND isarchive IS NOT NULL
                    AND isarchive <> 0
                ) AS completedScalar';
        $params = array(':project_id' => $projectID);
        $cmd = Yii::app()->db->createCommand();
        return $cmd->setText($sql)->queryRow(true, $params);
    }

    /**
     * Move project to a archive
     * @param integer $id the ID of the model to be updated
     * @throws CHttpException
     */
    public function actionSetArchived($id) {
        $model = Project::model()->findByPk($id);
        if ($model) {
            if ($model->archived == 1) {
                $model->archived = 0;
                Yii::app()->user->setFlash('success', Yii::t('main', 'The project was restored.'));
            } else {
                $model->archived = 1;
                Yii::app()->user->setFlash('success', Yii::t('main', 'The project was archived.'));

                $user = User::current();
                if ($id == $user->current_project_id) {
                    $user->current_project_id = null;
                    $user->save();
                    User::updateCurrent();
                }
            }

            $model->save();
            $this->redirect(Yii::app()->createUrl('/projects'));
            Yii::app()->end();
        }
        throw new CHttpException(400, 'Invalid request.');
    }

    /**
     * Delete project completely
     * @param integer $id the ID of the model to be updated
     * @throws CHttpException
     */
    public function actionDeleteProject($id) {
        $model = Project::model()->findByPk($id);
        if ($model) {
            $model->delete();

            Yii::app()->user->setFlash('success', Yii::t('main', 'The project was deleted.'));

            $user = User::current();
            if ($id == $user->current_project_id) {
                $user->current_project_id = null;
                $user->save();
                User::updateCurrent();
            }

            $this->redirect(Yii::app()->createUrl('/projects'));
            Yii::app()->end();
        }
        throw new CHttpException(400, 'Invalid request.');
    }

    /**
     * 
     * @param type $id
     * @throws CHttpException
     */
    public function actionRemoveUser($id) {
        if (User::current()->isCompanyAdmin(Company::current())) {
            $project = Project::getCurrent();
            if (empty($project))
                $this->redirect(Yii::app()->createUrl('/settings/projects'));

            UserByProject::model()->deleteAllByAttributes(array(
                'user_id' => (int) $id,
                'project_id' => $project->project_id
            ));
            Yii::app()->end();
        }
        throw new CHttpException(403, 'You don\'t have permissions to access this area.');
    }

    /**
     * Action is used on the People page.
     * Allows to invite new user by email or add existing company user
     * to the current project
     */
    public function actionManagePeople() {
        $model = new InviteForm;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'invite-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        if (!empty($_POST['InviteForm'])) {

            $project = Project::getCurrent();
            if (empty($project))
                $this->redirect(Yii::app()->createUrl('/settings/projects'));

            if (!empty($_POST['InviteForm']['email'])) {
                //member invited user by email
                $_POST['User'] = $_POST['InviteForm'];
                $this->forward('/user/invite');
            } elseif (!empty($_POST['InviteForm']['user'])) {
                $userByProject = UserByProject::model()->findByAttributes(array(
                    'project_id' => $project->project_id,
                    'user_id' => (int) $_POST['InviteForm']['user'],
                ));
                if (empty($userByProject)) {
                    $userByProject = new UserByProject;
                    $userByProject->user_id = (int) $_POST['InviteForm']['user'];
                    $userByProject->project_id = $project->project_id;
                    $isAdmin = 0;
                    if (User::current()->isCompanyAdmin(Company::current())) {
                        if (isset($_POST['InviteForm']['isadmin']) && $_POST['InviteForm']['isadmin'] == 1)
                            $isAdmin = 1;
                    }
                    $userByProject->is_admin = $isAdmin;
                    if ($userByProject->save())
                        Yii::app()->user->setFlash('success', 'User was successfully added to project.');
                    else
                        Yii::app()->user->setFlash('error', 'An error has occurred while saving, please try again!');
                }
                else {
                    Yii::app()->user->setFlash('error', 'The user is already a member of this project.');
                }
            } else {
                throw new CHttpException(400, 'Invalid request.');
            }
            $this->redirect(Yii::app()->createUrl('project/people'));
        }
        $this->renderPartial('_inviteUsersForm', array('model' => $model), false, true);
    }

    /**
     * 
     */
    protected function showHelpForNewUsers() {
        Yii::import('application.controllers.SiteController');
        if (Yii::app()->request->cookies->contains(SiteController::BK_NEW_USER) && Yii::app()->request->cookies[SiteController::BK_NEW_USER]->value == 1) {
            //remove new user flag
            $cookie = new CHttpCookie(SiteController::BK_NEW_USER, 0);
            $cookie->expire = time() + 60 * 60 * 24 * 360; //360days
            Yii::app()->request->cookies[SiteController::BK_NEW_USER] = $cookie;
            $this->redirect($this->createUrl('/new/1'));
        }
    }

    /**
     * 
     * @throws CHttpException
     */
    public function actionGetUsersList() {
        $project = Project::getCurrent();
        if (empty($project))
            throw new CHttpException(400, 'Invalid request.');
        $users = $project->users;
        $data = array();
        if (!empty($users) && is_array($users)) {
            foreach ($users as $user) {
                $data['usernames'][] = $user->getUserName($user);
            }
        }
        $this->respond($data, ResponseType::JSON);
        Yii::app()->end();
    }

}

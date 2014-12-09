<?php

/**
 * NotificationController
 * @since 1.2
 */
class NotificationController extends Controller {

    /**
     * @var string Layout views
     */
    public $layout = '//layouts/column1';

    /**
     * Render updates page by current project
     */
    public function actionIndex() {
        // Check current project
        $project = Project::getCurrent();
        if (empty($project)) {
            $this->redirect($this->createUrl('/project/index'));
        }
        //Edit Project stuff
        $this->registerClientScriptFiles(array(
            array('src' => '/fileuploader.js', 'type' => 'script', 'pos' => CClientScript::POS_HEAD),
            array('src' => '/fileuploader.css', 'type' => 'css'),
            array('src' => '/js/project/index/common.js', 'type' => 'script', 'baseUrl' => Yii::app()->baseUrl)
                ), Yii::app()->assetManager->publish('protected/extensions/EAjaxUpload/assets'));
        // MixPanel events tracking
        MixPanel::instance()->registerEvent(MixPanel::UPDATES_PAGE_VIEW);
        $this->render('index');
    }

    /**
     * Render updates page by all project & users
     */
    public function actionAll() {
        // Change layout
        $this->layout = '//layouts/main';
        // Notification url
        Yii::app()->clientScript->registerScript('notificationUrl', 'window.notificationUrl = \'' . Yii::app()->createUrl('/notification/AllNotifications') . '\';', CClientScript::POS_HEAD);
        // MixPanel events tracking
        MixPanel::instance()->registerEvent(MixPanel::UPDATES_PAGE_VIEW);
        $this->render('all');
    }

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform actions
                'actions' => array(
                    'index',
                    'delete',
                    'notifications',
                    'allnotifications',
                    'all',
                ),
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        if (Yii::app()->request->isPostRequest || Yii::app()->request->isAjaxRequest) {
            // we only allow deletion via POST request
            $model = $this->loadModel($id);
            if ($model->user_id != User::current()->user_id)
                throw new CHttpException(400, 'Invalid request.');

            $model->delete();
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']))
                $this->redirect(array('/notification'));
        } else
            throw new CHttpException(400, 'Invalid request.');
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id) {
        $model = Notification::model()->findByPk((int) $id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Get notifications by current project & user
     */
    public function actionNotifications() {
        $sql = 'SELECT DISTINCT n.*, u.user_id, u.name, u.lname, u.facebook_id, u.profile_img'
                . ' FROM {{notification}} AS n'
                . ' JOIN ({{user}} AS u, {{bug}} AS b, {{user_by_project}} AS up)'
                . ' ON (n.user_id = u.user_id AND n.bug_id IS NOT NULL AND n.bug_id = b.id AND b.project_id = :current_project_id)'
                . ' WHERE n.user_id = :current_user_id'
                . ' ORDER BY n.notification_id DESC LIMIT 50';

        $command = Yii::app()->db->createCommand($sql);
        $command->params = array(
            ':current_user_id' => Yii::app()->user->id,
            ':current_project_id' => Project::getCurrent()->project_id,
        );
        $notifications = $command->queryAll();
        $this->respond($notifications);
    }

    /**
     * Get notifications by all project & users
     */
    public function actionAllNotifications() {
        $sql = 'SELECT DISTINCT n.*, u.user_id, u.name, u.lname, u.facebook_id, u.profile_img, p.name AS project_name'
                . ' FROM {{notification}} AS n'
                . ' JOIN ({{user}} AS u, {{bug}} AS b, {{user_by_project}} AS up, {{project}} AS p)'
                . ' ON (n.user_id = u.user_id AND n.bug_id IS NOT NULL AND n.bug_id = b.id AND p.project_id = b.project_id)'
                . ' ORDER BY n.notification_id DESC LIMIT 50';

        $command = Yii::app()->db->createCommand($sql);
        $notifications = $command->queryAll();
        $this->respond($notifications);
    }

}

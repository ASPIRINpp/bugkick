<?php
/**
 * Projects list
 * @author  Alexey kavshirko@gmail.com
 * @author Bogdan Bogomazov <b.bogomazov@gmail.com> (changes)
 * @since 1.1
 */
$provider = $model->gridSearch();
$provider->pagination = $pagination;
$this->widget('zii.widgets.CListView', array(
    'id' => 'projects-list',
    'dataProvider' => $provider,
    'itemView' => '_listViewItem',
    'ajaxUpdate' => true,
    'emptyText' => Yii::t('main', 'No projects yet, please create one to get started.'),
    'enablePagination' => true,
    'summaryText' => '',
    'pagerCssClass' => 'list-pager',
    'pager' => $pager,
));
?>
<div class="clear"></div>

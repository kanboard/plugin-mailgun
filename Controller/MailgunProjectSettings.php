<?php
namespace Kanboard\Plugin\Mailgun\Controller;
use Kanboard\Controller\BaseController;

/**
 *
 * @author   Max Eisel
 */
class MailgunProjectSettingsController extends BaseController
{
    /**
     * Mailgun Project Action Settings
     *
     * @access public
     * @param array $values
     * @param array $errors
     */
    public function show(array $values = array(), array $errors = array())
    {
        $this->response->html($this->helper->layout->project('MailgunProject:settings', array(
            'owners' => $this->projectUserRoleModel->getAssignableUsersList($project['id'], true),
	    'values' => array(
                'MailgunProject_catchall'   => $this->projectMetadataModel->get($project['id'], 'MailgunProject_catchall'),
                'project_id' => $_REQUEST['project_id'],
		),
            'project' => $project,
            'title' => t('Edit Mailgun project settings')
        )));
    }

    public function save()
    {
	    $values = $this->request->getValues();
	    $errors = array();
	    $project = $this->getProject();
	    $columnList =  $this->columnModel->getList($project['id']);

	    $this->projectMetadataModel->save($project['id'], array('MailgunProject_catchall' => $values["MailgunProject_catchall"]));

	    return $this->show($values, $errors);
    }

}

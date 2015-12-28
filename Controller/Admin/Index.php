<?php
/**
 ************************************************************************
 * @copyright 2015 David Lima
 * @license Apache 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 ************************************************************************
 */
namespace Fennec\Modules\Videos\Controller\Admin;

use \Fennec\Controller\Admin\Index as AdminController;
use \Fennec\Modules\Videos\Model\Video as VideoModel;

/**
 * Basic video importing module
 *
 * @author David Lima
 * @version r1.0
 */
class Index extends AdminController
{
    
    /**
     * Banner Model
     * @var \Fennec\Modules\Videos\Model\Video
     */
    private $model;

    /**
     * Initial setup
     */
    public function __construct()
    {
        parent::__construct();
    
        $this->model = new VideoModel();
    
        $this->moduleInfo = array(
            'title' => 'Videos'
        );
    }
    
    /**
     * Default action
     */
    public function indexAction()
    {
        $this->list = $this->model->getAll();
    }
    
    /**
     * Manage videos creating/updating
     */
    public function formAction()
    {
        if ($this->getParam('id')) {
            $id = $this->model->id = (int) $this->getParam('id');
            $video = $this->model->getByColumn('id', $id);
            if (count($video)) {
                $this->video = $video[0];
                foreach($this->video as $param => $value){
                    $this->$param = $value;
                }
                $this->url = $this->video->getFullUrl();
            } else {
                $link = $this->linkToRoute('admin-videos');
                header("Location: $link ");
            }
        }
        
        if ($this->isPost()) {
            try {
                foreach ($this->getPost() as $postKey => $postValue) {
                    $this->$postKey = $postValue;
                }
        
                $this->model->setTitle($this->getPost('title'));
                $this->model->setUrl($this->getPost('url'));
                $this->model->setDescription($this->getPost('description'));
                
                $this->result = $this->model->save();
                if (isset($this->result['errors'])) {
                    $this->result['result'] = implode('<br>', $this->result['errors']);
                }
            } catch (\Exception $e) {
                $this->exception = $e;
                $this->throwHttpError(500);
            }
        }
    }
    
    /**
     * Try to delete a video
     */
    public function deleteAction()
    {
        header("Content-Type: Application/JSON");
        
        $result = array(
            'errors' => true,
            'result' => $this->translate('Invalid request')
        );
        
        if ($this->getParam('id') && is_numeric($this->getParam('id'))) {
            try {
                $id = (int) $this->getParam('id');
                $this->model->remove($id);
                $result['errors'] = false;
                $result['result'] = $this->translate('Video removed');
            } catch (\Exception $e) {
                $result['result'] = $e->getMessage();
            }
        }
        
        print_r(json_encode($result));
    }
}

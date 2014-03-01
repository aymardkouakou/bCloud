<?php
App::uses('AppController', 'Controller');
/**
 * Groups Controller
 *
 * @property Group $Group
 * @property PaginatorComponent $Paginator
 */
class GroupsController extends AppController
{
    public function beforeFilter()
    {
        $this->ajaxFunc = array('get', 'add');
        parent::beforeFilter();
		
		if ((int) Configure::read('bCloud.Group.Admin') !== (int) $this->Auth->user('group_id')) {
			throw new NotFoundException();
		}
    }
    
/**
 * get method
 *
 * @return void
 */
    public function get()
    {
        $this->options['recursive'] = 0;
        $this->options['order'] = array($this->modelClass.'.created DESC');
        
        $this->request->data = $this->{$this->modelClass}->find('all', $this->options);
    }

/**
 * add method
 *
 * @return void
 */
    public function add()
    {
        if ($this->request->is('post')) {
            $check = 1;
            $this->{$this->modelClass}->create();
            
            if ($this->{$this->modelClass}->register($this->request->data)) {
                $check = 0;
                $response = __('%s a été créé avec succès.', $this->request->data[$this->modelClass]['name']);
            } else {
                $message = "";
                foreach ($this->{$this->modelClass}->validationErrors as $v) {
                    $message .= implode(' ', $v);
                }
                $response = __('%s', $message);
            }
            $return = array('check' => $check, 'response' => $response);
            echo json_encode($return);
            
            $this->render('/Elements/empty');
        }
    }

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function delete($id = null)
    {
        if (!$this->{$this->modelClass}->exists($id)) {
            throw new NotFoundException();
        }
        
        $data = $this->{$this->modelClass}->read(null, $id);
        $this->request->onlyAllow('post', 'delete');
        
        if ($this->{$this->modelClass}->delete()) {
            $this->Session->setFlash(
                __('%s a été supprimé avec succès.',$data[$this->modelClass]['name']),
                'default',
                array('class' => 'alertMessage inline success')
            );
        } else {
            $this->Session->setFlash(
                __('%s ne peut pas être supprimé.', $data[$this->modelClass]['name']),
                'default',
                array('class' => 'alertMessage inline error')
            );
        }
        return $this->redirect(array('controller' => 'pages', 'action' => 'display', 'dico'));
    }
}

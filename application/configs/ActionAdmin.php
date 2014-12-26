<?php

class Core_Controller_ActionAdmin extends Core_Controller_Action {
    
    protected $_identity;
    public $_sessionReserva;
    
    public function init() {
        parent::init();
        $this->_sessionReserva = new Zend_Session_Namespace('sessionReserva');
        $this->_helper->layout->setLayout('layout-admin');  

    }
    
    public function preDispatch() {     
        parent::preDispatch();
        
        $this->permisos();
        $this->_identity = Zend_Auth::getInstance()->getIdentity();                        
        $this->view->menu=$this->getMenu();        
        $this->view->identity=$this->_identity;
    }
    
    function permisos() {
        $auth = Zend_Auth::getInstance();
        $controller=$this->_request->getControllerName();
        if ($auth->hasIdentity()) {                    
        }else{
            if ($controller!='index') {
                $this->_redirect('/admin');
            }
        }
    }
    
    function getMenu() {
        $menu = array(
            'dashboard'=>
            array('class'=>'icad-dashb','url'=>'/dashboard','title'=>'Dashboard'),
//            'profile'=>
//            array('class'=>'icad-prod','url'=>'/profile','title'=>'Perfil'),
            'user'=>
            array('class'=>'icad-prom','url'=>'/user','title'=>'Usuarios'),
            'role'=>
            array('class'=>'icad-prom','url'=>'/role','title'=>'Roles')
        );
        return $menu;
    }
     
    public function auth($usuario,$password,$url=null) {              
  //      $dbAdapter = Zend_Registry::get('dbAdmin');
        //var_dump($dbAdapter);
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
        $authAdapter
            ->setTableName('Admin')
            ->setIdentityColumn('userName')
            ->setCredentialColumn('password')
            ->setIdentity($usuario)
            ->setCredential($password);
        try {
            $select = $authAdapter->getDbSelect();
            $select->where('state = 1');
            //echo $select->assemble(); //exit;
            //var_dump($authAdapter); exit;
            $result = Zend_Auth::getInstance()->authenticate($authAdapter);
            //var_dump($result); exit;
            if ($result->isValid()){
                $storage = Zend_Auth::getInstance()->getStorage();
                $bddResultRow = $authAdapter->getResultRowObject();
                $storage->write($bddResultRow);
                $msj = 'Bienvenido Usuario '.$result->getIdentity();
                //$this->_flashMessenger->success($msj);
                $this->_identity = Zend_Auth::getInstance()->getIdentity(); 
                if(!empty($url)){
                    $this->_redirect($url);
                }
                $return = true;
            } else { 
                switch ($result->getCode()) {
                    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                        $msj = 'El usuario no existe';
                        break;
                    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                        $msj = 'Password incorrecto';
                        break;
                    default:
                        $msj='Datos incorrectos';
                        break;
                }
                $this->_flashMessenger->warning($msj);
               
                $return = false;
            }
        } catch(Exception $e) {
            echo $e->getMessage();exit;
        }
        
        return $return;
    }
}

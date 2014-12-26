<?php

class Dedicatorias_IndexController extends Core_Controller_ActionDedicatorias {

    public function init() {
        parent::init();
         $this->_helper->layout->setLayout('dedicatorias/layout-avanzado');
//        $estaSuscrito = $this->estaSuscrito();
//        if ($estaSuscrito->estaSuscrito == false) {
//            $this->_redirect('/pe/fulltracks/suscripcion');
//        } else {
//            $catalogo = $this->_getParam('catalogo', '');
//            if ($estaSuscrito->ultimoCobro == date('Ymd') || $estaSuscrito->esFreeUser) {
//                if (isset($catalogo) && $catalogo != '') {
//                    $this->_redirect('/pe/fulltracks/confirmar-descarga?catalogo=' . $catalogo);
//                }
//            } else {
//                if (isset($catalogo) && $catalogo != '') {
//                    $this->_redirect('/pe/fulltracks/confirma-suscripcion-demanda?catalogo=' . $catalogo);
//                } else {
//                    $this->_redirect('http://m.entretenimiento.entel.pe/');
//                }
//            }
//        }
         $mensaje = $this->_getParam('mensaje', '');
         if(isset($mensaje) && $mensaje!=''){
             $this->view->mensaje = $this->_getParam('mensaje', '');
         }
        $this->view->SoapMusica = $this->listarMusica();
        $this->forward($this->mobileDetect());
    }

    public function indexAction() {
        $this->_redirect('/basico240');
    }

    public function basicoAction() {
        
    }

    public function basico240Action() {
        
    }

    public function basico360Action() {
        
    }

    public function avanzadoAction() {
        
    }

}

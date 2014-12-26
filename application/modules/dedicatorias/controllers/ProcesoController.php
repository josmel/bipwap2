<?php

class Dedicatorias_ProcesoController extends Core_Controller_ActionDedicatorias {

    public $_sessionDedicatoria;

    public function init() {
        parent::init();
        $this->_helper->layout->setLayout('dedicatorias/layout-avanzado');
        $this->_sessionDedicatoria = new Zend_Session_Namespace('sessionDedicatoria');
//         $this->_datosUsuario = $this->estaSuscrito();
    }

    public function generarMensajeAction() {
        if (isset($this->_sessionDedicatoria->detalleMensaje['numeroDestino'])) {
            $this->view->numeroDestino = $this->_sessionDedicatoria->detalleMensaje['numeroDestino'];
            $this->view->dedicatoria = $this->_sessionDedicatoria->detalleMensaje['dedicatoria'];
        }
        $this->view->action = '/pe/dedicatorias/detalle-mensaje';
    }

    public function legalAction() {
        
    }
    
     public function preSuscripcionAction() {
        $this->view->action = '/pe/dedicatorias/confirma-suscripcion';
    }

    public function suscripcionAction() {
        $this->view->action = '/pe/dedicatorias/confirma-suscripcion';
    }

    public function confirmaSuscripcionAction() {
         $this->view->action = '/pe/dedicatorias';
    }

    public function confirmacionEnvioAction() {
        $this->view->catalogo = $this->_sessionDedicatoria->detalleMensaje['codigo'];
        $this->view->artista = $this->_sessionDedicatoria->detalleMensaje['artista'];
        $this->view->tema = $this->_sessionDedicatoria->detalleMensaje['tema'];
        $this->view->action = '/pe/dedicatorias/enviar-dedicatoria';
        if ($this->_request->isPost()) {
            $dataForm = $this->_request->getPost();
            try {
                $this->view->numeroDestino = $this->_sessionDedicatoria->detalleMensaje['numeroDestino'];
                $this->view->dedicatoria = $this->_sessionDedicatoria->detalleMensaje['dedicatoria'];
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->_redirect('/pe/dedicatorias');
            }
        } else {
            $this->view->numeroDestino = $this->_sessionDedicatoria->detalleMensaje['numeroDestino'];
            $this->view->dedicatoria = $this->_sessionDedicatoria->detalleMensaje['dedicatoria'];
        }
    }

    public function enviarDedicatoriaAction() {
        if ($this->_request->isPost()) {
            $dataForm = $this->_request->getPost();
            try {
                $this->_redirect('/pe/dedicatorias?mensaje=2');
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->_redirect('/pe/dedicatorias');
            }
        } else {
            $this->view->numeroDestino = $this->_sessionDedicatoria->detalleMensaje['numeroDestino'];
            $this->view->dedicatoria = $this->_sessionDedicatoria->detalleMensaje['dedicatoria'];
        }
    }

    public function detalleMensajeAction() {
        $this->view->action = '/pe/dedicatorias/confirmacion-envio';
        $this->view->catalogo = $this->_sessionDedicatoria->detalleMensaje['codigo'];
        $this->view->artista = $this->_sessionDedicatoria->detalleMensaje['artista'];
        $this->view->tema = $this->_sessionDedicatoria->detalleMensaje['tema'];
        if ($this->_request->isPost()) {
            $dataForm = $this->_request->getPost();
            try {
                $this->_sessionDedicatoria->detalleMensaje['numeroDestino'] = $dataForm['numeroDestino'];  // Save Value
                $this->_sessionDedicatoria->detalleMensaje['dedicatoria'] = $dataForm['dedicatoria'];  // Save Value
                $this->view->numeroDestino = $this->_sessionDedicatoria->detalleMensaje['numeroDestino'];
                $this->view->dedicatoria = $this->_sessionDedicatoria->detalleMensaje['dedicatoria'];
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->_redirect('/pe/dedicatorias');
            }
        } else {
            $this->view->catalogo = $this->_sessionDedicatoria->detalleMensaje['codigo'];
            $this->view->artista = $this->_sessionDedicatoria->detalleMensaje['artista'];
            $this->view->tema = $this->_sessionDedicatoria->detalleMensaje['tema'];
            $this->view->numeroDestino = $this->_sessionDedicatoria->detalleMensaje['numeroDestino'];
            $this->view->dedicatoria = $this->_sessionDedicatoria->detalleMensaje['dedicatoria'];
        }
    }

    public function confirmarDedicatoriaAction() {
        if ($this->_request->isPost()) {
            $dataForm = $this->_request->getPost();
            try {
                $this->view->catalogo = $dataForm['catalogo'];
                $this->view->artista = $dataForm['artista'];
                $this->view->tema = $dataForm['tema'];
                $this->view->action = '/pe/dedicatorias/generar-mensaje';
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->_redirect('/pe/dedicatorias');
            }
        } else {
            $catalogo = $this->_getParam('catalogo', '');
            if (isset($catalogo) && $catalogo != '') {
                $detalleMusica = $this->_GetResultSoap->_obtenerCancionEnDedicatorias($catalogo);
                $this->_sessionDedicatoria->detalleMensaje['artista'] = $detalleMusica->obtenerCancionEnDedicatoriasResult->artista;
                $this->_sessionDedicatoria->detalleMensaje['tema'] = $detalleMusica->obtenerCancionEnDedicatoriasResult->tema;
                $this->_sessionDedicatoria->detalleMensaje['codigo'] = $detalleMusica->obtenerCancionEnDedicatoriasResult->codigo;
                $this->view->catalogo = $this->_sessionDedicatoria->detalleMensaje['codigo'];
                $this->view->artista = $this->_sessionDedicatoria->detalleMensaje['artista'];
                $this->view->tema = $this->_sessionDedicatoria->detalleMensaje['tema'];
                $this->view->action = '/pe/dedicatorias/generar-mensaje';
            } else {
                $this->_redirect('/pe/dedicatorias');
            }
        }
    }

}

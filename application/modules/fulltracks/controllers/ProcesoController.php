<?php

class Fulltracks_ProcesoController extends Core_Controller_ActionFulltracks {

    public function init() {
        parent::init();
        $this->_datosUsuario = $this->estaSuscrito();
    }

    public function suscripcionAction() {
        if ($this->_datosUsuario->estaSuscrito == false) {
            $this->view->action = '/pe/fulltracks/suscripcion';
            $this->view->mensajeConfirmacion = $this->_datosUsuario->mensajeConfirmacion;
            $this->view->numero = $this->_datosUsuario->numuser;
            if ($this->_request->isPost()) {
                if ($this->_datosUsuario->estaSuscrito == false) {
                    $suscribir = $this->suscribirAFulltracks();
                    if ($suscribir->estado == true) {
                        $this->_flashMessage->success($suscribir->mensajeSuscripcionOK);
                        $this->_redirect('/pe/fulltracks/confirma-suscripcion?su=ok');
                    } else {
                        $this->_flashMessage->success('Ud. ya se encuentra suscrito');
                        $this->_redirect('/pe/fulltracks');
                    }
                } else {
                    $this->_redirect('http://m.entretenimiento.entel.pe/');
                }
            }
        } else {
            $this->_redirect('/pe/fulltracks');
        }
    }

    public function confirmaSuscripcionAction() {

        // $esPeticionPost = false;
        if ($this->_request->isPost()) {
            //  $esPeticionPost = true;
            $cobrarFt = $this->cobrarSuscripcionFulltracks();
            if ($cobrarFt->estado == true) {
                $this->_ModelLog->saveCdrCobros(null, 1, 'suscripcion', 'fulltracks');
                $this->_redirect('/pe/fulltracks');
            } else {
                $this->_ModelLog->saveCdrCobros(null, 0, 'suscripcion', 'fulltracks');
                $this->redirect('http://m.entretenimiento.entel.pe/?estado=' . $cobrarFt->xbiResultado);
            }
        }

        $variableGet = $this->_getParam('su', '');
        if (isset($variableGet) && $variableGet == 'ok') {
            $this->view->action = '/pe/fulltracks/confirma-suscripcion';
        } else {
            $this->_redirect('http://m.entretenimiento.entel.pe/');
        }
    }

    public function confirmaSuscripcionDemandaAction() {

        if ($this->_request->isPost()) {
            $cobrarFt = $this->cobrarDemandaFulltracks();
            if ($cobrarFt->estado == true) {
                $this->_ModelLog->saveCdrCobros(null, 1, 'demanda', 'fulltracks');
                $this->_redirect('/pe/fulltracks');
            } else {
                $this->_ModelLog->saveCdrCobros(null, 0, 'demanda', 'fulltracks');
                $this->redirect('http://m.entretenimiento.entel.pe/?estado=' . $cobrarFt->xbiResultado);
            }
        } else {
            $catalogo = $this->_getParam('catalogo', '');
            if (isset($catalogo) && $catalogo != '') {
                $cobrarFt = $this->cobrarDemandaFulltracks();
                if ($cobrarFt->estado == true) {
                    $this->_ModelLog->saveCdrCobros('', 1, 'demanda', 'fulltracks');
                    $this->_redirect('/pe/fulltracks/confirmar-descarga?catalogo=' . $catalogo);
                } else {
                    $this->_ModelLog->saveCdrCobros('', 0, 'demanda', 'fulltracks');
                    $this->redirect('http://m.entretenimiento.entel.pe/?estado=' . $cobrarFt->xbiResultado);
                }
            }
        }
        $this->view->action = '/pe/fulltracks/confirma-suscripcion-demanda';
    }

    public function confirmarDescargaAction() {
        if ($this->_request->isPost()) {
            $dataForm = $this->_request->getPost();
            try {
                if ($this->_datosUsuario->ultimoCobro == date('Ymd') || $this->_datosUsuario->esFreeUser) {
                    
                } else {
                    $this->_redirect('/pe/fulltracks/confirma-suscripcion-demanda?catalogo=' . $dataForm['catalogo']);
                }
                if (isset($dataForm['descargar']) && $dataForm['descargar'] == 'true') {
                    $generarCodigoDescarga = $this->_GetResultSoap->_generarCodigoDescargaEnFulltracks($dataForm['catalogo'], $this->obtenerNumero());
                    $tiket = $generarCodigoDescarga->generarCodigoDescargaEnFulltracksResult;
                    $Match = $this->_config['resources']['view']['Match'];
                    $Utilcodificar = new Core_Utils_Utils();
                    $encodificado = $Utilcodificar->encode($Match . $tiket);
                    $this->_redirect('/pe/fulltracks/reproductor?dw=' . urlencode($encodificado));
                } else {
                    $this->view->catalogo = $dataForm['catalogo'];
                    $this->view->artista = $dataForm['artista'];
                    $this->view->tema = $dataForm['tema'];
                    $this->view->action = '/pe/fulltracks/confirmar-descarga';
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->_redirect('/pe/fulltracks');
            }
        } else {
            $catalogo = $this->_getParam('catalogo', '');
            $catalog = $this->_getParam('catalog', '');
            $descargar = $this->_getParam('descargar', '');
            if (isset($catalogo) && $catalogo != '') {
                $this->view->perfil = $this->obtenerPerfil();
                $detalleMusica = $this->_GetResultSoap->_obtenerCancionEnFulltracks($catalogo);
                $this->view->catalogo = $catalogo;
                $this->view->artista = $detalleMusica->obtenerCancionEnFulltracksResult->artista;
                $this->view->tema = $detalleMusica->obtenerCancionEnFulltracksResult->tema;
                $this->view->action = '/pe/fulltracks/confirmar-descarga';
            } elseif (isset($descargar) && $descargar == 'true') {
                if ($this->_datosUsuario->ultimoCobro == date('Ymd') || $this->_datosUsuario->esFreeUser) {
                    
                } else {
                    $this->_redirect('/pe/fulltracks/confirma-suscripcion-demanda?catalogo=' . $catalog);
                }
                $generarCodigoDescarga = $this->_GetResultSoap->_generarCodigoDescargaEnFulltracks($catalog, $this->obtenerNumero());
                $tiket = $generarCodigoDescarga->generarCodigoDescargaEnFulltracksResult;
                $Match = $this->_config['resources']['view']['Match'];
                $Utilcodificar = new Core_Utils_Utils();
                $encodificado = $Utilcodificar->encode($Match . $tiket);
                $this->_redirect('/pe/fulltracks/reproductor?dw=' . urlencode($encodificado));
            } else {
                $this->_redirect('/pe/fulltracks');
            }
        }
    }

    function descargaAction() {
        if ($this->_request->isPost()) {
            $dataForm = $this->_request->getPost();
            try {
                if (!empty($dataForm['codigoControl']) && (int) $dataForm['codigoControl'] > 0) {
                    $this->_ModelLog->saveCdrDescargas($dataForm['codigo'], $dataForm['codigoControl'], 'fulltracks');
                    $this->_GetResultSoap->_confirmarDescargaEnFulltracks($dataForm['codigoControl'], 1);
//                    $file = file_get_contents($this->_config['app']['downloadFt'] . '/' . $dataForm['codigo']);
//                    $this->getResponse()
//                            ->setBody($file)
//                            ->setHeader('Content-Type', 'audio/mpeg3')
//                            ->setHeader('Content-Disposition', 'attachment; filename="' . $dataForm['codigo'] . '"')
//                            ->setHeader('Content-Length', strlen($file));
                    header("Location: " . $this->_config['resources']['view']['urlDownload'] . '=' . $dataForm['codigoControl']);
                    $this->_helper->layout->disableLayout();
                    $this->_helper->viewRenderer->setNoRender(true);
                } else {
                    $this->_redirect('/pe/fulltracks');
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->_redirect('/pe/fulltracks');
            }
        } else {
            $codigoControl = $this->_getParam('codigoControl', '');
            $codigo = $this->_getParam('codigo', '');
            if (!empty($codigoControl) && (int) $codigoControl > 0) {
                $this->_ModelLog->saveCdrDescargas($codigo, $codigoControl, 'fulltracks');
                $this->_GetResultSoap->_confirmarDescargaEnFulltracks($codigoControl, 1);
                header("Location: " . $this->_config['resources']['view']['urlDownload'] . '=' . $codigoControl);
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
            } else {
                $this->_redirect('/pe/fulltracks');
            }
        }
    }

    function reproductorAction() {
        $this->_helper->layout->setLayout('fulltracks/layout-descarga');
        $codigoTiket = $this->_getParam('dw', '');
        if (isset($codigoTiket) && $codigoTiket != '') {
            $Utilcodificar = new Core_Utils_Utils();
            $decodificar = $Utilcodificar->decode(urldecode($codigoTiket));
            $codigoControl = preg_replace("/[^0-9]/", "", $decodificar);
            $DatosCatalogo = $this->_GetResultSoap->_obtenerControlDescargaEnFulltracks($codigoControl);
            if ($DatosCatalogo->obtenerControlDescargaEnFulltracksResult->numuser == $this->obtenerNumero()) {
                $this->view->action = '/pe/fulltracks/descarga';
                $this->view->perfil = $this->obtenerPerfil();
                $this->view->codigoControl = $codigoControl;
                $this->view->codigo = $DatosCatalogo->obtenerControlDescargaEnFulltracksResult->archivo;
            } else {
                $this->_redirect('/pe/fulltracks');
            }
        } else {




            $this->_redirect('/pe/fulltracks');
        }
    }

    public function jsonAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $id = $this->getParam('page');

         $data = array(array('sha' => "46f56ba4e11aebac4e7cfca3f87421e27a4f34e6",
                    'commit' => array('autor' => array("name" => "Perry Govier",
                                                      "email" => "govierp@gmail.com",
                                                       "date" => "2014-12-17T23:37:15Z"),
                                  'committer' => array("name" => "Perry Govier",
                                                      "email" => "govierp@gmail.com",
                                                       "date" => "2014-12-17T23:37:15Z")),
                   'message' => "docs(formatting): bringing attention to certain beta14 related concepts"),
             array('sha' => "46f56ba4e11aebac4e7cfca3f87421e27a4f34e6",
                    'commit' => array('autor' => array("name" => "Perry Govier",
                                                      "email" => "govierp@gmail.com",
                                                       "date" => "2014-12-17T23:37:15Z"),
                                  'committer' => array("name" => "Perry Govier",
                                                      "email" => "govierp@gmail.com",
                                                       "date" => "2014-12-17T23:37:15Z")),
                   'message' => "docs(formatting): bringing attention to certain beta14 related concepts"));

        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Content-type', 'application/json; charset=UTF-8', true)
                ->setHeader("access-control-allow-origin",'*')
                ->setHeader('Access-Control-Allow-Credentials','true')
                ->setHeader('Access-Control-Expose-Headers','ETag, Link, X-GitHub-OTP, X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset, X-OAuth-Scopes, X-Accepted-OAuth-Scopes, X-Poll-Interval')
                ->setHeader('Cache-Control','public, max-age=60, s-maxage=60')
                 ->setHeader('Vary', 'Accept-Encoding')
                 ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setHeader('X-Frame-Options', 'deny')
                ->setHeader('X-RateLimit-Limit', '60')
                ->setHeader('X-RateLimit-Remaining', '60')
                 ->setHeader('status', '304 Not Modified')
                ->setHeader('X-XSS-Protection', '1; mode=block')
//X-RateLimit-Reset	1419351044
//X-Served-By	d594a23ec74671eba905bf91ef329026
//access-control-allow-cred...	true
//content-security-policy	default-src 'none'
//	
                ->appendBody("callback(".json_encode($data). ")");
    }

}

<?php

namespace AppBundle\Controller;

use AppBundle\Clases\DSIController;
use AppBundle\Entity\Nivel;
use AppBundle\Entity\Modulo;
use Proxies\__CG__\AppBundle\Entity\Matricula;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Exception\ThrowingCasterException;


class ModulosNivelesController extends DSIController{
    //funcion encargada de redirigir al sub menu de las gestion de modulos y niveles
    /**
     * @Route("/admin/modulosyniveles", name="gmyn")
     */
    public function modulosnivelesAction(){
        return $this->render('AppBundle:admin:gmodulosniveles/mynPrincipal.html.twig');

    }

    /**
     * @Route("/admin/modulos", name="gmodulos")
     */
    public function gmodulosAction(){

        $repositorio = $this->getDoctrine()->getRepository('AppBundle:Modulo');
        $modulo = $repositorio->findAll();
        if($modulo) {
            return $this->render('AppBundle:admin:gmodulosniveles/gmodulos.html.twig', array('modulo' => $modulo));
        }else{
            throw $this->createNotFoundException("No se encontraron ningun modulo registrado");
        }
    }

    /**
     * @Route("/admin/infomodulo", name="infomod")
     */
    public function obtenerPorIdAction(){
        $request = $this->get('request');
        $idpasado = $request->get('idmodulo');
        $repo = $this->getDoctrine()->getRepository('AppBundle:Modulo');
        $mod = $repo->findOneBy(array('idmodulo'=>$idpasado));

        return new JsonResponse(array("idmod"=>$mod->getIdmodulo(),
            "nombremod"=>$mod->getNombremodulo(),
            "fechainicio"=>$mod->getFechainicio()->format('Y-m-d'),
            "fechafin"=>$mod->getFechafin()->format('Y-m-d'),
            "duracion"=>$mod->getDuracion()));
    }

    //FUNCION ENCARGADA DE GENERAR LA VISTA PARA INGRESAR UN NUEVO NIVEL
    /**
     * @Route("/admin/newmodulo", name="newmodulo")
     */
    public function nuevoModuloAction(){
        return $this->render('@App/admin/gmodulosniveles/formNuevoModulo.html.twig');
    }

    //FUNCION ENCARGADA DE REALIZAR EL INGRESO DE EL MODULO
    /**
     * @Route("/admin/nmod", name="nmod")
     */
    public function newModuloAction(Request $request){
        $em=$this->getDoctrine()->getManager();
        $repmod = $this->getDoctrine()->getRepository('AppBundle:Modulo');
        //Verifico el envio de form
        if($request->isMethod("POST")){
            $auxnombre=$request->get('nombreMod');
            $auxfini = $request->get('fini');
            $auxffin = $request->get('ffin');
            $fechai = date_create_from_format('Y-m-d',$auxfini);
            $fechaf = date_create_from_format('Y-m-d',$auxffin);
            $modulos = $repmod->buscarmodulos1($fechai,$fechaf,$auxnombre);
            if($modulos == null) {
                $var = strtotime($auxffin) - strtotime($auxfini);
                if ($var > 0) {
                    $dif = intval($var / 60 / 60 / 24);
                    $mod = new Modulo();
                    $mod->setNombremodulo($auxnombre);
                    $mod->setFechainicio($fechai);
                    $mod->setFechafin($fechaf);
                    $mod->setDuracion($dif);
                    $em->persist($mod);
                    $em->flush();
                    $this->MensajeFlash('exito','Modulo ingresado con exito');
                    return $this->redirectToRoute('gmodulos');
                } else {
                    $this->MensajeFlash('error','No se pudo ingresar modulo: la Fecha inicio debe ser menor que la la Fecha final' . $var);
                    return $this->redirectToRoute('newmodulo');
                }
            }else{
                $this->MensajeFlash('error','No se pudo ingresar modulo: Hay un modulo con el mismo nombre o con la misma fecha de inicio almacenado verificar fechas de fiinalizacion de ultimo modulo');
                return $this->redirectToRoute('newmodulo');
            }
        }//return $this->render('AppBundle:admin/gmodulosniveles:formNuevoModulo.html.twig');
        return;
    }

    //FUNCION ENCARGADA DE MODIFICAR DATOS DE UN MODULO
    /**
     * @Route("/admin/updatemod/{id}", name="actualizarmod")
     */
    public function actualizarmodAction(Request $request, $id){
        //$fechaactual = new \DateTime();
        $em= $this->getDoctrine()->getManager();
        $rep=$this->getDoctrine()->getRepository('AppBundle:Modulo');
        $auxmod = $rep->find($id);
        if($request->isMethod("POST")){
            //$auxid = $request->get('idmodulo');
            if($auxmod){
                $auxnombre=$request->get('nombremodulo');
                $auxfechai=$request->get('fini');
                $auxfechaf=$request->get('ffin');
                $fechai = date_create_from_format('Y-m-d',$auxfechai);
                $fechaf = date_create_from_format('Y-m-d',$auxfechaf);
                $var = strtotime($auxfechai)-strtotime("now");
                if($var>0){
                    $auxmod->setNombremodulo($auxnombre);
                    $auxmod->setFechainicio($fechai);
                    $auxmod->setFechafin($fechaf);
                    $em->persist($auxmod);
                    $em->flush();
                    $this->MensajeFlash('exito','Modulo actualizado correctamente');
                    return $this->redirectToRoute('gmodulos');
                }else{
                   $this->mensajeFlash('error','No se pudo actualizar el modulo ya que ya ha iniciado o finalizado');
                   return $this->redirectToRoute('gmodulos');
                }
            }else{
                throw $this->createNotFoundException('No se obtuvo resultados de la busqueda a BD');
            }
        }else{
            return $this->render('AppBundle:admin/gmodulosniveles:formupdatemodulo.html.twig',array('modulo'=>$auxmod,'id'=>$id));
        }
    }

    //FUNCION ENCARGADA DE ELIMINAR UN MODULO
    /**
     * @Route("/admin/deletemod/{id}", name="deletemod")
     */
    public function deletemodAction($id){
        $em = $this->getDoctrine()->getManager();
        $rep = $this->getDoctrine()->getRepository('AppBundle:Modulo');
        $auxmod = $rep->find($id);
        $auxnivel = $auxmod->getNivelnivel()->get('nombrenivel');
        if($auxnivel==null){
            $em->remove($auxmod);
            $em->flush();
            $this->MensajeFlash('exito','Modulo eliminado de forma exitosa');
            return $this->redirectToRoute('gmodulos');
        }else{
            $this->MensajeFlash('error','No se puede eliminar el modulo ya que tiene niveles asignados');
            return $this->redirectToRoute('gmodulos');
        }
    }

    /*-----------------------------------------------------------------------------------------------------------------*/
/*Seccion dedicada para la gestion de niveles*/
    /**
     * @Route("/admin/niveles", name="gniveles")
     */
    public function gnivelesAction(){
        $em = $this->getDoctrine()->getManager();
        $auxnivel = new Nivel();
        $repo = $em->getRepository('AppBundle:Nivel');
        $auxnivel= $repo->findAll();
        return $this->render('AppBundle:admin/gmodulosniveles:gniveles.html.twig',array('listNivel'=>$auxnivel));
    }

    //FUNCION QUE DA LA VISTA PARA INGRESAR UN NUEVO NIVEL
    /**
     * @Route("/admin/newnivel", name="newnivel")
     */
    public function newNivelAction(Request $request){
        $repo = $this->getDoctrine()->getRepository('AppBundle:Modulo');
        $fecha = new \DateTime('now');
        $mod = $repo->modulosxfecha($fecha);
        $em= $this->getDoctrine()->getManager();
        $niv = $em->getRepository('AppBundle:Nivel');
        if($request->isMethod("POST")){
            $auxfechai = $request->get('fini');
            $auxfechaf = $request->get('ffin');
            $fechai = date_create_from_format('Y-m-d',$auxfechai);
            $fechafin = date_create_from_format('Y-m-d',$auxfechaf);
            $selmodulo = $request->get('smodulo');
            $var = strtotime($auxfechaf) - strtotime($auxfechai);
            if($var > 0){
                if (!$selmodulo == null) {
                    $auxmod = $repo->buscarmodulos1($auxfechai, $auxfechaf, $selmodulo);
                    $auxniv = $niv->findOneBy(array("fechainicio"=>$fechai,"fechafin"=>$fechafin));
                    if(!$auxniv) {
                        if ($auxmod) {
                            $dif = intval($var / 60 / 60 / 24);
                            $niv = new Nivel();
                            $niv->setNombrenivel($request->get("nombreNivel"));
                            $niv->setFechainicio($fechai);
                            $niv->setFechafin($fechafin);
                            $niv->setDuracion($dif);
                            $em->persist($niv);
                            $em->flush();
                            $auxmod->addNivelnivel($niv);
                            $em->flush();
                            $this->MensajeFlash('exito','Nivel ingresado de forma exitosa');
                            return $this->redirectToRoute('newnivel');
                        }else{
                            $this->MensajeFlash('error','No se puede ingresar el nivel ya que sus fechas no corresponde con las del modulo');
                            return $this->render('AppBundle:admin/gmodulosniveles:formNuevoNivel.html.twig', array('mod' => $mod));
                        }
                    }else{
                        $this->MensajeFlash('error','Ya existe un nivel para las fechas ingresadas');
                        return $this->render('AppBundle:admin/gmodulosniveles:formNuevoNivel.html.twig', array('mod' => $mod));
                    }
                }
            }else{
                $this->MensajeFlash('error','Fecha de Inicio debe ser menor que la Fecha fin');
                return $this->render('AppBundle:admin/gmodulosniveles:formNuevoNivel.html.twig', array('mod' => $mod));
            }
        }
        return $this->render('AppBundle:admin/gmodulosniveles:formNuevoNivel.html.twig',array('mod'=>$mod));

    }

    //FUNCION ENCARGADA DE ACTUALIZAR UN NIVEL
    /**
     * @Route("/admin/updatenivel/{id}", name="updatenivel")
     */
    public function updatenivelAction(Request $request,$id){
        $em1 = $this->getDoctrine()->getManager();
        $em = $this->getDoctrine()->getRepository('AppBundle:Nivel');
        $auxniv = $em->find($id);
        if($request->isMethod('POST')){
            if($auxniv){
                $auxnombre=$request->get('nombrenivel');
                $auxfechai=$request->get('fini');
                $auxfechaf=$request->get('ffin');
                $fechai = date_create_from_format('Y-m-d',$auxfechai);
                $fechaf = date_create_from_format('Y-m-d',$auxfechaf);
                $var = strtotime($auxfechai)-strtotime("now");
                if($var>0){
                    $auxniv->setNombrenivel($auxnombre);
                    $auxniv->setFechainicio($fechai);
                    $auxniv->setFechafin($fechaf);
                    $em1->persist($auxniv);
                    $em1->flush();
                    $this->MensajeFlash('exito','Modulo actualizado correctamente');
                    return $this->redirectToRoute('gniveles');
                }else{
                    $this->MensajeFlash('error','No se pudo actualizar el modulo ya que ya ha iniciado o finalizado');
                    return $this->redirectToRoute('gniveles');
                }
            }else{
                throw $this->createNotFoundException('No se obtuvo resultados de la busqueda a BD');
            }
        }else{
            return $this->render('AppBundle:admin/gmodulosniveles:formupdatenivel.html.twig',array("nivel"=>$auxniv, "id"=>$id));
        }
    }

    //FUNCION ENCARGADA DE ELIMINAR UN NIVEL
    /**
     * @Route("/admin/deletenivel/{id}", name="deletenivel")
     */
    public function deletenivelAction($id){
        $em = $this->getDoctrine()->getManager();
        $repnivel = $this->getDoctrine()->getRepository('AppBundle:Nivel');
        $repmatricula = $this->getDoctrine()->getRepository('AppBundle:Matricula');
        $auxniv = $repnivel->find($id);
        $auxmatricula = $repmatricula->findOneBy(array("nivelnivel"=>$id));

        $auxfechanivel = $auxniv->getFechainicio();
        $fechaactual = new \DateTime("now");

        if($auxfechanivel>$fechaactual) {
            if ($auxmatricula == null) {
                $em->remove($auxniv);
                $em->flush();
                $this->MensajeFlash('exito','Nivel eliminado de forma exitosa');
                return $this->redirectToRoute('gniveles');
            } else {
                $this->MensajeFlash('error','No se puede eliminar el nivel ya que tiene alumnos inscritos en el');
                return $this->redirectToRoute('gniveles');
            }
        }else{
            $this->MensajeFlash('error','No se puede eliminar el nivel ya que esta ya ha iniciado o finalizado');
            return $this->redirectToRoute('gniveles');
        }
    }



    /**
     * @Route("/admin/infonivel", name="infoniv")
     */
    public function obtenernivporidAction(){
        $request = $this->get('request');
        $idpasado = $request->get('idmodulo');
        $repo = $this->getDoctrine()->getRepository('AppBundle:Nivel');
        $niv = $repo->findOneBy(array('idnivel'=>$idpasado));

        return new JsonResponse(array("idmod"=>$niv->getIdnivel(),
            "nombremod"=>$niv->getNombrenivel(),
            "fechainicio"=>$niv->getFechainicio()->format('Y-m-d'),
            "fechafin"=>$niv->getFechafin()->format('Y-m-d'),
            "duracion"=>$niv->getDuracion()));
    }
}
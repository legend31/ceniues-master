<?php
/**
 * Created by PhpStorm.
 * User: Kcrez
 * Date: 20/9/2015
 * Time: 8:10 PM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Docente;
use AppBundle\Entity\Usuario;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DocenteController extends Controller
{
    /**
     * @Route("/admin/docentes", name="dhome")
     */
    public function docenteHomeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if($request->isMethod("POST")) {
            $docentes = $em->getRepository('AppBundle:Docente')->find($request->get("carnet"));
            $docente = array($docentes);
            if(!$docentes)
            {
                return new Response($this->container->get('templating')->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docentes, 'mensaje'=>3 )));
            }
            return $this->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docente, 'mensaje'=>10));
        }
        else {
            $docentes = $em->getRepository('AppBundle:Docente')->findAll();
            if($request->get('estado') == 1) {
                return new Response($this->container->get('templating')->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docentes, 'mensaje'=>1 )));
            }
            elseif($request->get('estado') == 2) {
                return new Response($this->container->get('templating')->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docentes, 'mensaje'=>2 )));
            }
            elseif($request->get('estado') == 4) {
                return new Response($this->container->get('templating')->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docentes, 'mensaje'=>4 )));
            }
            elseif($request->get('estado') == 5) {
                return new Response($this->container->get('templating')->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docentes, 'mensaje'=>5 )));
            }
            elseif($request->get('estado') == 6) {
                return new Response($this->container->get('templating')->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docentes, 'mensaje'=>6 )));
            }
            elseif($request->get('estado') == 7) {
                return new Response($this->container->get('templating')->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docentes, 'mensaje'=>7 )));
            }
            elseif(!$docentes)
            {
                return new Response($this->container->get('templating')->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docentes, 'mensaje'=>3 )));
            }
            return new Response($this->container->get('templating')->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docentes, 'mensaje'=>null)));
        }
    }

    /**
     * @Route("/admin/adocentes", name="agregarD")
     */
    public function agregarDocenteAction(Request $request)
    {
        if($request->isMethod("POST")){
            $em=$this->getDoctrine()->getManager();

            //Verificar existencia de docente
            $vCarnet = $em->getRepository('AppBundle:Docente')->find($request->get("cdoc"));
            $vDui = $em->getRepository('AppBundle:Docente')->findOneBy(array('dui'=>$request->get("ddoc")));
            if($vCarnet && $vDui) {
                return $this->redirectToRoute('dhome', array('estado'=>6));
            }
            elseif($vCarnet){
                return $this->redirectToRoute('dhome', array('estado'=>1));
            }
            elseif($vDui) {
                return $this->redirectToRoute('dhome', array('estado'=>7));
            }

            //Creando nuevo docente
            $d = new Docente();
            $d->setNombredocente($request->get("ndoc"));
            $d->setApellidodocente($request->get("adoc"));
            $d->setDui($request->get("ddoc"));
            $d->setDirecciondocente($request->get("ddoc"));
            $d->setCarnetdocente($request->get("cdoc"));
            $d->setTelefono($request->get("tdoc"));
            $d->setFechanacimiento(new \DateTime($request->get("fdoc")));
            $d->setEstado(1);

            $em->persist($d);
            $em->flush();

            //Creando usuario docente
            $u = new Usuario();
            $u->setNomusuario($request->get("cdoc"));
            $u->setIsactive(1);
            $u->setTipoUsuariotipoUsuario($em->getRepository('AppBundle:TipoUsuario')->find(3));
            $u->setEmailusuario($request->get("edoc"));
            //Cifra la contraseņa
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($u);
            $password = $encoder->encodePassword($request->get("cdoc"), $u->getSalt());
            $u->setPassword($password);

            $em->persist($u);
            $em->flush();

            return $this->redirectToRoute('dhome', array('estado'=>2));
        }
        else {
            return $this->render('AppBundle:docente:creardocente.html.twig');
        }
    }

    /**
     * @Route("/admin/cdocente", name="consultarD")
     */
    public function consultarDocenteAction(Request $request)
    {
        if($request->isMethod("POST")) {
            $em = $this->getDoctrine()->getManager();
            $busquedaPor = $request->get("selS");
            $docentes = 'no encuentra';
            if($busquedaPor == 'carnet') {
                $docentes = $em->getRepository('AppBundle:Docente')->find($request->get("parB"));
                $docente = array($docentes);
            }
            elseif($busquedaPor == 'dui') {
                $docentes = $em->getRepository('AppBundle:Docente')->findOneBy(array('dui'=>$request->get("parB")));
                $docente = array($docentes);
            }
            elseif($busquedaPor == 'nombredocente') {
                $docentes = true;
                $nombres = $request->get("parB");
                $nombre = explode(' ', $nombres);
                $cuenta = count($nombre);
                if($cuenta > 1 && $nombre[1] != ''){
                    $pnombre = $nombre[0];
                    $snombre = $nombre[1];
                    $docente = $em->getRepository('AppBundle:Docente')->findBy(array('primernombredocente'=>$pnombre, 'segundonombredocente'=>$snombre));
                    if(!$docente) {
                        return $this->render('AppBundle:docente:buscardocente.html.twig', array('docentes'=>'no encuentra'));
                    }
                }
                else {
                    $pnombre = $nombre[0];
                    $docente = $em->getRepository('AppBundle:Docente')->findBy(array('primernombredocente'=>$pnombre));
                    if(!$docente) {
                        $docente = $em->getRepository('AppBundle:Docente')->findBy(array('segundonombredocente'=>$pnombre));
                        if(!$docente) {
                            return $this->render('AppBundle:docente:buscardocente.html.twig', array('docentes'=>'no encuentra'));
                        }
                    }
                }
            }
            elseif($busquedaPor == 'apellidodocente') {
                $docentes = true;
                $apellidos = $request->get("parB");
                $ape = explode(' ', $apellidos);
                $cuenta = count($ape);
                if($cuenta > 1 && $ape[1] != ''){
                    $pape = $ape[0];
                    $sape = $ape[1];
                    $docente = $em->getRepository('AppBundle:Docente')->findBy(array('primerapellidodocente'=>$pape, 'segundoapellidodocente'=>$sape));
                    if(!$docente) {
                        return $this->render('AppBundle:docente:buscardocente.html.twig', array('docentes'=>'no encuentra'));
                    }
                }
                else {
                    $pape = $ape[0];
                    $docente = $em->getRepository('AppBundle:Docente')->findBy(array('primerapellidodocente'=>$pape));
                    if(!$docente) {
                        $docente = $em->getRepository('AppBundle:Docente')->findBy(array('segundoapellidodocente'=>$pape));
                        if(!$docente) {
                            return $this->render('AppBundle:docente:buscardocente.html.twig', array('docentes'=>'no encuentra'));
                        }
                    }
                }
            }
            if(!$docentes)
            {
                return $this->render('AppBundle:docente:buscardocente.html.twig', array('docentes'=>'no encuentra' ));
            }
            return $this->render('AppBundle:docente:buscardocente.html.twig', array('docentes'=>$docente));
        }
        return $this->render('AppBundle:docente:buscardocente.html.twig', array('docentes'=>null));
    }

    /**
     * @Route("/admin/mdocente/{carnet}", name="modificarD")
     */
    public function modificarDocenteAction(Request $request, $carnet)
    {
        $em = $this->getDoctrine()->getManager();
        if($request->isMethod("POST")) {
            //verificar existencia
            if($carnet != $request->get("cdoc")) {
                if ($em->getRepository('AppBundle:Docente')->find($request->get("cdoc"))) {
                    return $this->redirectToRoute('dhome', array('estado' => 1));
                }
            }
            $newDui = $request->get('ddoc');
            $buscarDui = $em->getRepository('AppBundle:Docente')->find($carnet);
            if($buscarDui->getDui() != $newDui) {
                if($em->getRepository('AppBundle:Docente')->findOneBy(array('dui'=>$newDui))) {
                    return $this->redirectToRoute('dhome', array('estado' => 7));
                }
            }

            //Actualizando docente
            $d = $em->getRepository('AppBundle:Docente')->find($carnet);
            $d->setNombredocente($request->get("ndoc"));
            $d->setApellidodocente($request->get("adoc"));
            $d->setDui($request->get("ddoc"));
            $d->setDirecciondocente($request->get("rdoc"));
            $d->setCarnetdocente($request->get("cdoc"));
            $d->setTelefono($request->get("tdoc"));
            $d->setFechanacimiento(new \DateTime($request->get("fdoc")));
            $d->setEstado(1);

            $em->persist($d);
            $em->flush();

            //Modificando usuario docente
            if($this->getDoctrine()->getRepository('AppBundle:Usuario')->findOneBy(array('nomusuario'=>$request->get("cdoc")))) {
                $u = $em->getRepository('AppBundle:Usuario')->findOneBy(array('nomusuario'=>$carnet));
            }
            else { //crear usuario si no existe
                $u = new Usuario();
            }

            $u->setNomusuario($request->get("cdoc"));
            $u->setIsactive(1);
            $u->setTipoUsuariotipoUsuario($em->getRepository('AppBundle:TipoUsuario')->find(3));
            $u->setEmailusuario($request->get("edoc"));
            //Cifra la contraseņa
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($u);
            $password = $encoder->encodePassword($request->get("cdoc"), $u->getSalt());
            $u->setPassword($password);

            $em->persist($u);
            $em->flush();

            return $this->redirectToRoute('dhome', array('estado'=>5));
        }
        else {
            $docentes = $em->getRepository('AppBundle:Docente')->findOneBy(array('carnetdocente'=>$request->get("carnet")));
            $email = $this->getDoctrine()->getRepository('AppBundle:Usuario')->findOneBy(array('nomusuario'=>$request->get("carnet")));
            $html = $this->container->get('templating')->render('AppBundle:docente:pdoc.html.twig', array('docente' => $docentes, 'correo' => $email, 'carnet' => $carnet));

            return new Response($html);
        }
    }

    /**
     * @Route("/admin/dnivel", name="docn")
     */
    public function docenteNivelAction()
    {
        $em = $this->getDoctrine()->getManager();
        $docentes = $em->getRepository('AppBundle:Docente')->findAll();
        $clases = $em->getRepository('AppBundle:Clase')->findAll();

        if(!$docentes)
        {
            return $this->render('AppBundle:docente:docenteNivel.html.twig', array('form'=>$docentes, 'error'=>1));
        }
        elseif(!$clases)
        {
            return $this->render('AppBundle:docente:docenteNivel.html.twig', array('form'=>$docentes, 'error'=>2));
        }
        $i=0;
        foreach($clases as $clase) {
            if($clase->getDocenteCarnetdocente()) {
                $d[$i] = $clase->getDocenteCarnetdocente()->getCarnetdocente();
                $i++;
            }
        }
        $i=0;
        foreach($docentes as $docente) {
            if($docente->getEstado()) {
                $dc[$i] = $docente->getCarnetdocente();
                $dn[$i] = $docente->getNombredocente();
                $i++;
            }
        }
        $nomdoc= $dn;
        $n = array_diff($dc,$d);
        foreach ($n as $key => $value) {
            if(isset($n[$key])){
                unset($dn[$key]);
            }
        }
        $envio=array_diff($nomdoc,$dn);
        $send = array($n, $envio);
        return $this->render('AppBundle:docente:docenteNivel.html.twig', array('form'=>$send, 'error'=>null, 'clases'=>$clases, 'carnet'=>$n));
    }

    /**
     * @Route("/admin/edocente", name="eliminarD")
     */
    public function eliminarDocenteAction(Request $request)
    {
        $id = $request->get("crnE");
        $em=$this->getDoctrine()->getManager();
        $docente = $em->getRepository('AppBundle:Docente')->find($id);
        $docente->setEstado(0);
        $em->flush();
        return $this->redirectToRoute('dhome');
    }

    /**
     * @Route("/admin/adocente", name="activarD")
     */
    public function activarDocenteAction(Request $request)
    {
        $id = $request->get("carnet");
        $em=$this->getDoctrine()->getManager();
        $docente = $em->getRepository('AppBundle:Docente')->find($id);
        $docente->setEstado(1);
        $em->flush();
        return $this->redirectToRoute('dhome');
    }

    /**
     * @Route("/ldocente", name="documentoD")
     */
    public function reporteDocenteAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $docentes = $em->getRepository('AppBundle:Docente')->findAll();

        if(!$docentes)
        {
            throw $this->createNotFoundException('No se encontro ningun docente');
        }
        return new Response($this->container->get('templating')->render('AppBundle:reportes:listadoDocentes.html.twig', array('TituloPagina' => 'Docentes inscritos', 'form' => $docentes)));
    }

    /**
     * @Route("/andocente", name="documento2D")
     */
    public function reporteDocente2Action()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $clases = $em->getRepository('AppBundle:Clase')->findAll();

        if(!$clases)
        {
            return $this->render('@App/docente/distriDoc.html.twig', array('TituloPagina' => 'Docentes en nivel', 'form' => $clases, 'msj'=>'error'));
        }
        return $this->render('@App/docente/distriDoc.html.twig', array('TituloPagina' => 'Docentes en nivel', 'form' => $clases, 'msj'=>null));
    }

    /**
     * @Route("/admin/detallesD", name="detallesD")
     */
    public function detallesPorCarnetAction(){
        $request = $this->get('request');
        $dui = $request->get('duidoc');
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Docente');
        $docente = $repo->findOneBy(array('dui'=>$dui));

        return new JsonResponse(array("carnetdoc"=>$docente->getCarnetdocente(),
            "nombre"=>$docente->getNombredocente(),
            "apellido"=>$docente->getApellidodocente(),
            "duidoc"=>$docente->getDui(),
            "direccion"=>$docente->getDirecciondocente(),
            "fnac"=>$docente->getFechanacimiento()->format('Y:m:d'),
            "ntel"=>$docente->getTelefono()));
    }

    /**
     * @Route("/defHor", name="definirH")
     */
    public function definirHorarioAction(Request $request) {
        if($request->isMethod("POST")) {
            $em = $this->getDoctrine()->getManager();
            if($this->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRADOR')) {
                $buscar = $em->getRepository("AppBundle:Docente")->find($request->get('parB'));
                if(!$buscar) {
                    return $this->render("@App/docente/definirHorario.html.twig", array('docente'=>'2'));
                }
                $envio = $buscar->getCarnetdocente();
                return $this->render("@App/docente/definirHorario.html.twig", array('docente'=>$envio));
            }
            else {
                return $this->render("@App/docente/definirHorario.html.twig", array('docente'=>null));
            }
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_DOCENTE')) {
            return $this->render("@App/docente/definirHorario.html.twig", array('docente'=>'1'));
        }
        else {
            return $this->render("@App/docente/definirHorario.html.twig", array('docente'=>null));
        }
    }

    /**
     * @Route("/gHor", name="guardarH")
     */
    public function guardarHorarioAction(Request $request) {
        if($request->isMethod("POST")) {
            $em = $this->getDoctrine()->getManager();
            $d = $em->getRepository("AppBundle:Docente")->find($request->get('crnt'));
            if(!$d) {
                return $this->render("@App/docente/definirHorario.html.twig", array('docente'=>'4'));
            }
            $hm = $request->get('horS').":".$request->get('minS')."-am";
            $hv = $request->get('hor2').":".$request->get('min2')."-pm";
            $sabD = $request->get('sabD');
            $domD = $request->get('domD');
            $d->setHorasD($hm." ".$hv);
            $d->setDiasD($sabD." ".$domD);
            $em->flush();
            return $this->render("@App/docente/definirHorario.html.twig", array('docente'=>'3'));
        }
        else {
            return $this->render("@App/docente/definirHorario.html.twig", array('docente'=>null));
        }
    }

    /**
     * @Route("/modifD", name="modD2")
     */
    public function modificarDocenteLateralAction(Request $request){
        if($request->isMethod("POST")) {
            $em = $this->getDoctrine()->getManager();
            $carnet = $request->get('carnet');
            $docente = $em->getRepository('AppBundle:Docente')->find($carnet);
            if(!$docente) {
                return $this->render('@App/docente/modifcarDLateral.html.twig', array('modif'=>'no encuentra'));
            }
            else {
                $email = $this->getDoctrine()->getRepository('AppBundle:Usuario')->findOneBy(array('nomusuario'=>$request->get("carnet")));
                return $this->render('@App/docente/pdoc.html.twig', array('docente' => $docente, 'correo' => $email, 'carnet' => $request->get('carnet')));
            }
        }
        else {
            return $this->render('@App/docente/modifcarDLateral.html.twig', array('modif'=>null));
        }
    }

    /**
     * @Route("/elimD", name="elimL2")
     */
    public function eliminarLateralAction(Request $request){
        if($request->isMethod("POST")) {
            $em = $this->getDoctrine()->getManager();
            $carnet = $request->get('carnet');
            $docente = $em->getRepository('AppBundle:Docente')->find($carnet);
            if(!$docente) {
                return $this->render('@App/docente/elimLateral.html.twig', array('modif'=>'no encuentra'));
            }
            else {
                //si encuentra docente lo deshabilita
                $docente->setEstado(0);
                $em->flush();
                return $this->render('@App/docente/elimLateral.html.twig', array('modif'=>'elim'));
            }
        }
        else {
            return $this->render('@App/docente/elimLateral.html.twig', array('modif'=>null));
        }
    }

    /**
     * @Route("/js2", name="js2")
     */
    public function javascriptSelect(Request $request){
        if($request->isMethod('POST')){
            $rep2 = $this->getDoctrine()->getRepository('AppBundle:Clase');
            $idpasado = $request->get('idniv');
            $clase = $rep2->findBy(array('nivelnivel'=>$idpasado));
            //$c = array();
            foreach($clase as $clas){
                //array_push($c,$clas->getHorario());
                $c["seccion"]=$clas->getSeccion()->getNombreseccion();
                $d["local"]=$clas->getLocallocal()->getNombrelocal();
            }
            echo json_encode($c);
            //$this->mensajeflash(date("Y-m-d(h:i:s)",$c["horario"]));
            return new Response();

        }
    }

    /**
     * @Route("/adnivel", name="asignarD")
     */
    public function asignarSelect(Request $request){
        if($request->isMethod('POST')){
            $rep2 = $this->getDoctrine()->getRepository('AppBundle:Clase');
            $idpasado = $request->get('nivel');
            $clase = $rep2->findBy(array('nivelnivel'=>$idpasado));

            return $this->render('@App/docente/docenteNivel.html.twig', array('form'=>null, 'error'=>null, 'clases'=>null));
        }
    }

    /**
     * @Route("/rdnivel", name="reasignarD")
     */
    public function reasignarSelect(Request $request){
        $rep2 = $this->getDoctrine()->getRepository('AppBundle:Clase');
        $carnet = $request->get('carnet');
        $clase = $rep2->findBy(array('docenteCarnetdocente'=>$carnet));
        $niveles = $this->getDoctrine()->getRepository('AppBundle:Nivel')->findAll();

        return $this->render('@App/docente/reasignarDocente.html.twig', array('clase'=>$clase, 'niveles'=>$niveles));
    }
}
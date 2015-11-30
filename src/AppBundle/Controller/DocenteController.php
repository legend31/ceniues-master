<?php
/**
 * Created by PhpStorm.
 * User: Kcrez
 * Date: 20/9/2015
 * Time: 8:10 PM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Docente;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
            if(!$docente)
            {
                throw $this->createNotFoundException('No se encontro ningun docente');
            }
            return $this->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docente));
        }
        else {
            $docentes = $em->getRepository('AppBundle:Docente')->findAll();
            if(!$docentes)
            {
                throw $this->createNotFoundException('No se encontro ningun docente');
            }
            return new Response($this->container->get('templating')->render('AppBundle:docente:gestionarDocente.html.twig', array('docentes'=>$docentes)));
        }
    }

    /**
     * @Route("/admin/adocentes", name="agregarD")
     */
    public function agregarDocenteAction(Request $request)
    {
        if($request->isMethod("POST")){
            $em=$this->getDoctrine()->getManager();
            $d=new Docente();
            $d->setNombredocente($request->get("ndoc"));
            $d->setApellidodocente($request->get("adoc"));
            $d->setDui($request->get("ddoc"));
            $d->setDirecciondocente($request->get("ddoc"));
            $d->setCarnetdocente($request->get("cdoc"));
            $d->setTelefono($request->get("tdoc"));
            $d->setEstado(1);

            $em->persist($d);
            $em->flush();
            return $this->redirectToRoute('dhome');
        }
        else {
            return $this->render('AppBundle:docente:creardocente.html.twig');
        }
    }

    /**
     * @Route("/cdocente", name="consultarD")
     */
    public function consultarDocenteAction()
    {
        return new Response($this->container->get('templating')->render('AppBundle:docente:buscardocente.html.twig'));
    }

    /**
     * @Route("/mdocente", name="modificarD")
     */
    public function modificarDocenteAction()
    {
        $html = $this->container->get('templating')->render('AppBundle:docente:cruddocente.html.twig', array('TituloPagina' => 'Consultar Docente','form' => $form->createView()));

        return new Response($html);
    }

    /**
     * @Route("/dnivel", name="docn")
     */
    public function docenteNivelAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $docentes = $em->getRepository('AppBundle:Docente')->findAll('primernombredocente');
        $locales = $em->getRepository('AppBundle:Local')->findAll();
        $niveles = $em->getRepository('AppBundle:Nivel')->findAll();
        $seccion = $em->getRepository('AppBundle:Seccion')->findAll();

        if(!$docentes||!$locales||!$niveles)
        {
            throw $this->createNotFoundException('No se encontro ningun dato relacionado');
        }

        return new Response($this->container->get('templating')->render('AppBundle:docente:docenteNivel.html.twig', array('form'=>$docentes)));
    }

    /*/**
     * @Route("/idocente", name="insertarD")
     */
    /*public function insertarDocenteAction(Request $request)
    {
        $em=$this->getDoctrine()->getEntityManager();
        if($request->isMethod("POST")){
            $d=new Docente();
            $d->setNombredocente($request->get("ndoc"));
            $d->setApellidodocente($request->get("adoc"));
            $d->setDui($request->get("ddoc"));
            $d->setDirecciondocente($request->get("ddoc"));
            $d->setCarnetdocente($request->get("cdoc"));
            $d->setTelefono($request->get("tdoc"));

            $em->persist($d);
            $em->flush();
            return $this->redirectToRoute('dhome');
        }
    }*/

    /**
     * @Route("/edocente", name="eliminarD")
     */
    public function eliminarDocenteAction(Request $request)
    {
        $id = $request->get("carnet");
        $em=$this->getDoctrine()->getEntityManager();
        $em->persist($id);
        $em->flush();
        return $this->redirectToRoute('dhome');
    }
    //Nada
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
     * @Route("/detallesD", name="detallesD")
     */
    public function detallesPorCarnetAction(){
        $request = $this->get('request');
        $carnet = $request->get('carnetdocente');
        $repo = $this->getDoctrine()->getRepository('AppBundle:Docente');
        $docente = $repo->findOneBy(array('carnetdocente'=>$carnet));

        return new JsonResponse(array("carnetdoc"=>$docente->getCarnetdocente(),
            "nombre"=>$docente->getNombredocente(),
            "apellido"=>$docente->getApellidodocente(),
            "dui"=>$docente->getDui(),
            "direccion"=>$docente->getDirecciondocente(),
            "fnac"=>$docente->getFechanacimiento(),
            "ntel"=>$docente->getTelefono()));
    }
}
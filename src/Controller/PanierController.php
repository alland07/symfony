<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PanierController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $panier = $em->getRepository(Panier::class)->findAll();

        return $this->render('panier/index.html.twig', [
            'panier' => $panier,
        ]);
    }
     /**
     * @Route("/delete_panier/{id}", name="delete_panier")
     */
    public function delete(Panier $panier=null, TranslatorInterface $translator)
    {
        if($panier!=null){
            $em = $this->getDoctrine()->getManager();
            $em->remove($panier);
            $em->flush();
            $this->addFlash("success",$translator->trans('panier.success'));

        }
       return $this->redirectToRoute('home');
    }
}

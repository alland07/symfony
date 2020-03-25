<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\PanierType;
use App\Form\ProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\CssSelector\XPath\TranslatorInterface as XPathTranslatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="produit")
     */
    public function index(Request $request, TranslatorInterface $translator)
    {
        $pdo =$this->getDoctrine()->getManager();
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $fichier = $form->get('photo')->getData();

            if($fichier){
                $nomFile = uniqid().'.'.$fichier->guessExtension();

                try{
                    $fichier->move(
                        $this->getParameter('upload'),
                        $nomFile
                    );
                }
                catch(FileException $e){
                    $this->addFlash("danger",$translator->trans('produit.error'));

                    return $this->redirectToRoute('produit');
                }
                $produit->setPhoto($nomFile);
            }

            $pdo->persist($produit);
            $pdo->flush();
            $this->addFlash("success",$translator->trans('produit.success'));

        }

        $produits = $pdo->getRepository(Produit::class)->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'new_produit' => $form->createView(),
        ]);
    }
    /**
     * @Route("/produit/fiche-produit/{id}", name="fiche")
     */
    public function ajouter(Request $request, TranslatorInterface $translator, Produit $produit=null)
    {
        if ($produit !=null){

        $pdo =$this->getDoctrine()->getManager();
        //On crée l'objet panier ici afin qu'il ne soit plus vide à partir du moment ou on ajoute une quantité de produit
        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $panier->setEtat(false);
            $panier->setDate(new \DateTime('NOW'));
            //Comme la valeur ne peut pas être null il faut set le produit également
            $panier->setProduit($produit);
            $pdo->persist($panier);
            $pdo->flush();
            $this->addFlash("success",$translator->trans('produit.success'));
        }

        return $this->render('produit/produit.html.twig', [
            'produits' => $produit,
            'ajout_produit' => $form->createView(),
        ]);
    }
    else{
        //Produit n'existe pas
        $this->addFlash("danger",$translator->trans('produit.notexist'));
        return $this->redirectToRoute('produit');
    }
    }

    /**
     * @Route("/produit/delete/{id}" ,name="delete_produit")
     */
    public function delete(Produit $produit=null, TranslatorInterface $translator){

        if($produit != null){
            $pdo = $this->getDoctrine()->getManager();
            $pdo->remove($produit);
            $pdo->flush();
            $this->addFlash("success","Produit supprimé");
        }
        return $this->redirectToRoute('produit');
        $this->addFlash("success",$translator->trans('produit.introuvable'));
    }
}

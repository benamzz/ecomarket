<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(EntityManagerInterface $em, Request $r): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($r);
        if ($form->isSubmitted() && $form->isValid()) {

            $photo = $form->get('photo')->getData();
            dd($photo);

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $newFilename = uniqid() . '.' . $photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $this->getParameter('upload_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('app_produit');
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $produit->setPhoto($newFilename);
            }
            $produit->setphoto(
                new File($this->getParameter('upload_dir') . '/' . $produit->getphoto())
            );
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit ajouté');
        }

        $produits = $em->getRepository(Produit::class)->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'ajout' => $form->createView()
        ]);
    }

    #[Route('/produit/{id}', name: 'produit')]
    public function show(Produit $produit = null, Request $r, EntityManagerInterface $em)
    {
        if ($produit == null) {
            $this->addFlash('danger', 'Produit introuvable');
            return $this->redirectToRoute('app_produit');
        }

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($r);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit mis à jour');
        }

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'edit' => $form->createView()
        ]);
    }

    #[Route('/produit/delete/{id}', name: 'produit_delete')]
    public function delete(Produit $produit = null, EntityManagerInterface $em)
    {
        if ($produit == null) {
            $this->addFlash('danger', 'Produit introuvable');
        } else {
            $em->remove($produit);
            $em->flush();

            $this->addFlash('warning', 'Produit supprimé');
        }

        return $this->redirectToRoute('app_produit');
    }
}

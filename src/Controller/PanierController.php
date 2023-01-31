<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Utilisateur;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session, ProduitRepository $produitRepository, EntityManagerInterface $em): Response
    {;
        // Obtenir l'utilisateur actuel
        $utilisateur = $this->getUser();

        // Créer un nouvel objet Panier
        $panier = new Panier();
        $panier->setUtilisateur($utilisateur);

        // Enregistrer le nouvel objet Panier dans la base de données
        $em->persist($panier);
        $em->flush();
        $this->addFlash('success', 'Panier ajouté');

        $panierContenu = $session->get("panier", []);

        $dataPanier = [];
        $total = 0;

        foreach ($panierContenu as $id => $quantite) {
            $produit = $produitRepository->find($id);
            $dataPanier[] = [
                "produit" => $produit,
                "quantite" => $quantite
            ];
            $total += $produit->getPrix() * $quantite;
        }

        return $this->render('panier/index.html.twig', [
            "dataPanier" => $dataPanier,
            "total" => $total,
            "utilisateur" => $utilisateur
        ]);
    }

    #[Route('/panier/add/{id}', name: 'panier_add')]
    public function add(Produit $produit, SessionInterface $session)
    {
        // on recupère le panier actuelle
        $panierContenu = $session->get("panier", []);
        $id = $produit->getId();
        if (!empty($panierContenu[$id])) {
            $panierContenu[$id]++;
        } else {
            $panierContenu[$id] = 1;
        }
        // sauvegarde en session
        $session->set("panier", $panierContenu);

        return $this->redirectToRoute("app_panier");
    }
    #[Route('/panier/remove/{id}', name: 'panier_remove')]
    public function remove(Produit $produit, SessionInterface $session)
    {
        // on recupère le panier actuelle

        $panierContenu = $session->get("panier", []);
        $id = $produit->getId();
        if (!empty($panierContenu[$id])) {
            if ($panierContenu[$id] > 1) {
                $panierContenu[$id]--;
            } else {
                unset($panier[$id]);
            }
        }
        // sauvegarde en session
        $session->set("panier", $panierContenu);

        return $this->redirectToRoute("app_panier");
    }
    #[Route('/panier/delete/{id}', name: 'panier_delete')]
    public function delete(Produit $produit, SessionInterface $session)
    {
        // on recupère le panier actuelle

        $panierContenu = $session->get("panier", []);
        $id = $produit->getId();
        if (!empty($panierContenu[$id])) {
            unset($panierContenu[$id]);
        }
        // sauvegarde en session
        $session->set("panier", $panierContenu);

        return $this->redirectToRoute("app_panier");
    }
    #[Route('/panier/delete/', name: 'panier_delete_all')]
    public function deleteAll(SessionInterface $session)
    {
        // on recupère le panier actuelle

        $session->remove("panier");

        return $this->redirectToRoute("app_panier");
    }

    #[Route('/panier/validation/', name: 'panier_valider')]
    public function valider(Panier $panier)
    {
        // on recupère le panier actuelle
        $panier->setEtat(true);
        dd($panier);
        return $this->render("app_panier_validation");
    }
}

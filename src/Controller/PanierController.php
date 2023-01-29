<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        $panier = $session->get("panier", []);

        $dataPanier = [];
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $produit = $produitRepository->find($id);
            $dataPanier[] = [
                "produit" => $produit,
                "quantite" => $quantite
            ];
            $total += $produit->getPrix() * $quantite;
        }
        return $this->render('panier/index.html.twig', compact("dataPanier", "total"));
    }
    #[Route('/panier/add/{id}', name: 'panier_add')]
    public function add(Produit $produit, SessionInterface $session)
    {
        // on recupère le panier actuelle

        $panier = $session->get("panier", []);
        $id = $produit->getId();
        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }
        // sauvegarde en session
        $session->set("panier", $panier);

        return $this->redirectToRoute("app_panier");
    }
    #[Route('/panier/remove/{id}', name: 'panier_remove')]
    public function remove(Produit $produit, SessionInterface $session)
    {
        // on recupère le panier actuelle

        $panier = $session->get("panier", []);
        $id = $produit->getId();
        if (!empty($panier[$id])) {
            if ($panier[$id] > 1) {
                $panier[$id]--;
            } else {
                unset($panier[$id]);
            }
        }
        // sauvegarde en session
        $session->set("panier", $panier);

        return $this->redirectToRoute("app_panier");
    }
    #[Route('/panier/delete/{id}', name: 'panier_delete')]
    public function delete(Produit $produit, SessionInterface $session)
    {
        // on recupère le panier actuelle

        $panier = $session->get("panier", []);
        $id = $produit->getId();
        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }
        // sauvegarde en session
        $session->set("panier", $panier);

        return $this->redirectToRoute("app_panier");
    }
    #[Route('/panier/delete/', name: 'panier_delete_all')]
    public function deleteAll(SessionInterface $session)
    {
        // on recupère le panier actuelle

        $session->remove("panier");

        return $this->redirectToRoute("app_panier");
    }
}

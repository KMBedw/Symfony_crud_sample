<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\ProductsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    Private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $products = $this->doctrine->getRepository(Products::class)->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product', name: 'new_product')]
    public function new(Request $request, ManagerRegistry $doctrine): Response
{
    $product = new Products();
    $form = $this->createForm(ProductsType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $doctrine->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }
    return $this->render('product/new.html.twig', [
        'form' => $form->createView(),
    ]);
}
#[Route('/product/edit/{id}', name: 'product_edit')]
public function edit(Request $request, ManagerRegistry $doctrine, $id): Response
{
    $entityManager = $doctrine->getManager();
    $product = $entityManager->getRepository(Products::class)->find($id);
    
    $form = $this->createForm(ProductsType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'Product updated successfully.');
        return $this->redirectToRoute('app_home');
    }
    return $this->render('product/edit.html.twig', [
        'form' => $form->createView(),
        'products' => $product,
    ]);
}
#[Route('/product/delete/{id}', name: 'product_delete')]
public function delete(ManagerRegistry $doctrine, $id): Response
{
    $entityManager = $doctrine->getManager();
    $product = $entityManager->getRepository(Products::class)->find($id);
    

    if (!$product) {
        throw $this->createNotFoundException('Pas de produit trouvé pour cet id'. $id);
    }
    $entityManager->remove($product);
    $entityManager->flush();
    $this->addFlash('Reussi', 'Produit suprimé');
    return $this->redirectToRoute('app_home');
}
}

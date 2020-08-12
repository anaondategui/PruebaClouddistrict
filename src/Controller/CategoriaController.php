<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CategoriaController extends AbstractController
{
    /**
     * @Route("/categoria", name="createcategoria", methods={"POST"})
     */
    public function createCategoria(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $content = json_decode($request->getContent(),true);
        $categoriaNombre=$content['nombre'];
        $categoria = new Categoria();
        $categoria->setNombre($categoriaNombre);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($categoria);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Categoría guardada con el ID; '.$categoria->getId());
    }
}

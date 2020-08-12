<?php

namespace App\Controller;

use App\Entity\Categoria;
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

        $entityManager->persist($categoria);
        $entityManager->flush();

        return new Response('Categoría guardada con el ID; '.$categoria->getId());
    }
}

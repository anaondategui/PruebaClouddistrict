<?php

namespace App\Controller;

use App\Entity\Producto;
use App\Repository\CategoriaRepository;
use ContainerCy5MCYP\getCategoriaControllerService;
//use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ProductoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class ProductoController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var PaginatorInterface
     */
    private $paginator;
    private $pagelimit;

    public function __construct(SerializerInterface $serializer, PaginatorInterface $paginator)
    {
       $this->serializer = $serializer;
       $this->paginator = $paginator;
       $this->pagelimit = 3;

    }

    /**
     * @Route("/productoNombre", name="getProductoByName", methods={"GET"})
     */
    public function getProductoByNombre(Request $request, ProductoRepository $productRepository)
    {
        if (! $request->query->has('busca')){
            return new Response("No sabemos que buscar, por favor pasa por POST un nombre a buscar con key \'busca\'", 500);
        }
        if (! $request->query->has('pagina')){
            $pagina = 1;
        }
        else{
            $pagina = $request->query->get('pagina');
        }
        $nombreAbuscar = $request->query->get('busca');
        $result = $productRepository->findByName($nombreAbuscar);
        $pagination = $this->paginator->paginate($result, $pagina, $this->pagelimit);

        $reports = $this->serializer->serialize($pagination, 'json');
        return new Response($reports);

    }

    /**
     * @Route("/productoCategoria", name="getProductoByCategoria", methods={"GET"})
     */
    public function getProductoByCategoria(Request $request, ProductoRepository $productRepository)
    {
        if (! $request->query->has('busca')){
            return new Response("No sabemos que buscar, por favor pasa por POST una categoría a buscar con key \'busca\'", 500);
        }
        $pagina = $this->getPage($request);
        $nombreAbuscar = $request->query->get('busca');
        $result = $productRepository->findByCategoria($nombreAbuscar);
        $pagination = $this->paginator->paginate($result, $pagina, $this->pagelimit);
        $reports = $this->serializer->serialize($pagination, 'json');
        return new Response($reports);
    }

    /**
     * @Route("/productoPrecio", name="getProductoByPrice", methods={"GET"})
     * @param Request $request
     * @param ProductoRepository $productRepository
     * @return Response
     */
    public function getProductoByPrice(Request $request, ProductoRepository $productRepository )
    {
        $preciomenor = $request->query->has('preciomenor')? $request->query->get('preciomenor'):999999;
        $preciomayor = $request->query->has('preciomayor')?$request->query->get('preciomayor'):0;
        $pagina = $this->getPage($request);

        $result = $productRepository->findByPrice($preciomenor, $preciomayor);
        $pagination = $this->paginator->paginate($result, $pagina, $this->pagelimit);
        $reports = $this->serializer->serialize($pagination, 'json');
        return new Response($reports);
    }

    /**
     * @Route("/producto", name="createProducto", methods={"POST"})
     */
    public function createProducto (Request $request, CategoriaRepository $categoriaRepository)
    {
        /*Comprobamos que es un JSON*/
        if ($this->containsHeader($request, 'Content-Type', 'application/json')) {
            $entityManager = $this->getDoctrine()->getManager();
            $productoJson = json_decode($request->getContent(), true);
            if (!$productoJson) {
                return new Response("Tienes que pasar un JSON con los datos de producto");
            }
        }
        /* $this->compruebaJson($request); */

        $producto = new Producto();
        $producto->setNombre($productoJson['nombre']); /* TODO poner control de que exite y si no avisar */
        $producto->setDescripcion(array_key_exists('descripcion', $productoJson) ? $productoJson['descripcion'] : "");
        $producto->setImagen(array_key_exists('imagen', $productoJson) ? $productoJson['imagen'] : "");
        $producto->setPrecio($productoJson['precio']);
        $producto->setPrecioIva($productoJson['precio'] * 1.21); /*TODO meter el iva en la bbdd o de alguna forma de multiplicar mas dinamica*/
        $categoriaObject = $categoriaRepository->findOneBy(['nombre' => $productoJson['categoria']]);

        if ($categoriaObject === null) { /*  TODO revisar si en este caso, o creamos categoria nueva, o falla... */
            return new Response("Categoria, no definida, use una categoria existente", 500);
        }
        $producto->setCategoria($categoriaObject);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($producto);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Producto guardado con el ID; ' . $producto->getId());
    }

    private function containsHeader(Request $request, $name, $value)
    {
        return 0 === strpos($request->headers->get($name), $value);
    }
    /*
     * TODO funcion, para comprobar las keys de la bbdd y ver si el Json las trae todas...
    private function compruebaJson (Request $jsonData)
    {
    }
    */
    /**
     * @param Request $request
     * @return bool|float|int|string|null
     */
    private function getPage(Request $request)
    {
        if (!$request->query->has('pagina')) {
            $pagina = 1;
        } else {
            $pagina = $request->query->get('pagina');
        }
        return $pagina;
    }


}

<?php

namespace App\Controller;

use App\Entity\Producto;
use App\Repository\CategoriaRepository;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ProductoRepository;


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
     * @param Request $request
     * @param ProductoRepository $productRepository
     * @return Response
     */
    public function getProductoByNombre(Request $request, ProductoRepository $productRepository)
    {
        if (! $request->query->has('busca')){
            return new Response("No sabemos que buscar, por favor pasa por POST un nombre a buscar con key \'busca\'", 500);
        }
        $pagina = $this->getPage($request);
        $nombreAbuscar = $request->query->get('busca');
        $result = $productRepository->findByName($nombreAbuscar);
        $reports = $this->paginateResults($result, $pagina);
        return new Response($reports);
    }

    /**
     * @Route("/productoCategoria", name="getProductoByCategoria", methods={"GET"})
     * @param Request $request
     * @param ProductoRepository $productRepository
     * @return Response
     */
    public function getProductoByCategoria(Request $request, ProductoRepository $productRepository)
    {
        if (! $request->query->has('busca')){
            return new Response("No sabemos que buscar, por favor pasa por POST una categoría a buscar con key \'busca\'", 500);
        }
        $pagina = $this->getPage($request);
        $nombreAbuscar = $request->query->get('busca');
        $result = $productRepository->findByCategoria($nombreAbuscar);
        $reports = $this->paginateResults($result, $pagina);
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
        $reports = $this->paginateResults($result, $pagina);
        return new Response($reports);
    }

    /**
     * @Route("/producto", name="createProducto", methods={"POST"})
     * @param Request $request
     * @param CategoriaRepository $categoriaRepository
     * @return Response
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

        $producto = new Producto();
        /* TODO poner control de que existen todas las variables y si no avisar */
        $producto->setNombre($productoJson['nombre']);
        $producto->setDescripcion(array_key_exists('descripcion', $productoJson) ? $productoJson['descripcion'] : "");
        $producto->setImagen(array_key_exists('imagen', $productoJson) ? $productoJson['imagen'] : "");
        $producto->setPrecio($productoJson['precio']);
        /*TODO meter el iva en la bbdd o de alguna forma de multiplicar mas dinamica*/
        $producto->setPrecioIva($productoJson['precio'] * 1.21);
        $categoriaObject = $categoriaRepository->findOneBy(['nombre' => $productoJson['categoria']]);

        if ($categoriaObject === null) { /*  TODO revisar si en este caso, o creamos categoria nueva, o falla... */
            return new Response("Categoria, no definida, use una categoria existente", 500);
        }
        $producto->setCategoria($categoriaObject);

        $entityManager->persist($producto);
        $entityManager->flush();

        return new Response('Producto guardado con el ID; ' . $producto->getId());
    }

    private function containsHeader(Request $request, $name, $value)
    {
        return 0 === strpos($request->headers->get($name), $value);
    }

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

    /**
     * @param array $result
     * @param int|null $pagina
     * @return string
     */
    private function paginateResults(array $result, ?int $pagina): string
    {
        $pagination = $this->paginator->paginate($result, $pagina, $this->pagelimit);
        $reports = $this->serializer->serialize($pagination, 'json');
        return $reports;
    }
}

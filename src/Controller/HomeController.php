<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use HttpResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/HomeController.php',
        ]);
    }

    #[Route('/product', name: 'add_product', methods: ['POST'])]
    public function add(
        #[MapRequestPayload]
        Product $product, EntityManagerInterface $manager): JsonResponse
    {
        $manager->persist($product);
        $manager->flush();

        return $this->json([$product]);
    }

    #[Route('/products', name: 'get_products', methods: ['GET'])]
    public function getProducts(ProductRepository $productRepository): JsonResponse
    {
        return $this->json($productRepository->findAll());
    }

    #[Route('/products/{id}', name: 'get_product', methods: ['GET'])]
    public function getProduct(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($request->get('id'));
        if($product === null) {
            return $this->json([
                "message" => "Product not found"
            ],
                404
            );
        }
        return $this->json($product);
    }

    #[Route('/products/delete/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function deleteProduct(Request $request, ProductRepository $productRepository, EntityManagerInterface
    $manager):JsonResponse
    {
        $product = $productRepository->find($request->get('id'));
        if($product === null) {
            return $this->json([
                "message" => "Product not found"
            ],
                404
            );
        }
        $manager->remove($product);
        $manager->flush();

        return $this->json([
            "message" => "The product was successfully deleted!",
            'product' => $product
        ]);
    }

    #[Route('/product/update', name: 'update_product', methods: ['PUT'])]
    public function updateProduct(ProductRepository $productRepository, EntityManagerInterface $manager, Request $request)
    :JsonResponse
    {
        $newProduct = json_decode($request->getContent(), true);
        $product = $productRepository->findOneBy(["id"=>$newProduct["id"]]);

        $product->setName($newProduct["name"]);
        $product->setPrice($newProduct["price"]);
        $product->setDescription($newProduct["description"]);
        $product->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($product);
        $manager->flush();

        return $this->json($product);
    }


}

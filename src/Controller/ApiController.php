<?php

namespace App\Controller;

use App\Entity\Pokemon;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use JMS\Serializer\SerializerBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;



class ApiController extends AbstractController
{
    /**
     * @Route("/api", name="api")
     */
    public function index(ParameterBagInterface $params, EntityManagerInterface $em, PokemonRepository $repository)
    {
        $serializer = SerializerBuilder::create()->build();
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $data = $serializer->decode(file_get_contents($params->get('directory')."/file/pokemon.csv"), 'csv');
        //Enregistrer les data en bdd 
        $this->setDatabase($data, $em, $repository);
        $pokemons = $repository->findAll();
        if($pokemons){
            return new JsonResponse($data, 201, [], false);
        }
        return new JsonResponse('Une erreur s\'est produite !!', 404, [], false);
        
        
    }

    //remplir la base de données si elle est vide
    public function setDatabase($data, EntityManagerInterface $em, PokemonRepository $repository){
        if(!$repository->count([])){
            foreach ($data as $item){
                $pokemon = new Pokemon();
                $pokemon->setName("test");
                $pokemon->setType1($item["Type 1"]);
                $pokemon->setType2($item["Type 2"]);
                $pokemon->setTotal($item["Total"]);
                $pokemon->setHp($item["HP"]);
                $pokemon->setAttack($item["Attack"]);
                $pokemon->setDefense($item["Defense"]);
                $pokemon->setSp($item["Sp"]);
                $pokemon->setSpeed($item["Speed"]);
                $pokemon->setGeneration($item["Generation"]);
                $pokemon->setLegendary($item["Legendary"]);
                $em -> persist($pokemon);
            }
            $em ->flush();
        }
    }



    /**
    * @Route("/resetdatabase")
    */    
    public function resetDatabase(EntityManagerInterface $em, PokemonRepository $repository)
    {
        $pokemons = $repository->findAll();
        //vider la base de données
        foreach ($pokemons as $pokemon) {
            $em->remove($pokemon);
        }
        return new JsonResponse('OK', 200, [], false);
    }
}

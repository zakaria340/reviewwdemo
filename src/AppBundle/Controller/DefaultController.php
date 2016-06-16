<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Item;
use AppBundle\Entity\Urls;

class DefaultController extends Controller {

  /**
   * @Route("/add", name="addpage")
   */
  public function addAction(Request $request) {

//    $item = new Item();
//    $item->setIdApi('39964');
//
//    // Création d'une première candidature
//
//    $urls = new Urls();
//    $urls->setName('VideoMega.tv');
//    $urls->setvideo('<iframe width="100%" height="500" frameborder="0" mozallowfullscreen="true" webkitallowfullscreen="true" allowfullscreen="true" src="http://videomega.tv/view.php?ref=085117070052057078115050109118118109050115078057052070117085&amp;width=700&amp;height=430&amp;val=1" scrolling="no" id="iframe-embed"></iframe>');
//    $urls->setQualite('Cam');
//    $urls->setItem($item);
//
//    // Étape 1 : On « persiste » l'entité
//
//    $em = $this->getDoctrine()->getManager();
//    $em->persist($item);
//    $em->persist($urls);
//    $em->flush();
  }

  /**
   * @Route("/", name="homepage")
   */
  public function indexAction(Request $request) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getPopular(array('page' => 1));
    $TopRatedMovies = $TopRatedMovies['results'];
    $firstMovies = array_slice($TopRatedMovies, 0, 3);
    $movies = array_slice($TopRatedMovies, 3, 4);
    $TopRatedTV = $client->getTvApi()->getPopular(array('page' => 1));
    $TopRatedTV = $TopRatedTV['results'];
    $firstTvs = array_slice($TopRatedTV, 0, 3);
    $tvs = array_slice($TopRatedTV, 3, 4);
    return $this->render('AppBundle:Core:index.html.twig', array(
          'firstMovies' => $firstMovies,
          'firstTvs' => $firstTvs,
          'movies' => $movies,
          'tvs' => $tvs,
    ));
  }

}

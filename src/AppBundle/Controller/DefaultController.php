<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sunra\PhpSimple\HtmlDomParser;

class DefaultController extends Controller {

  /**
   * @Route("/dcma", name="dcma")
   */
  public function dcmaAction(Request $request) {
return $this->render('AppBundle:Core:dcma.html.twig');
  }
  
    /**
   * @Route("/contact", name="contact")
   */
  public function contactAction(Request $request) {
return $this->render('AppBundle:Core:contact.html.twig');
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

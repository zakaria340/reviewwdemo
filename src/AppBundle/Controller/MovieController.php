<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MovieController extends Controller {

  /**
   * @Route(
   *     "/movies/{page}",
   *     name="popularMovies",
   *    defaults={"page" = 1},
   *     requirements={
   *         "page": "\d*"
   *     }
   * )
   */
  public function popularmoviesAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getPopular(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'popularMovies',
      'pages_count' => $TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Movie:popularmovies.html.twig', array(
        'movies' => $TopRatedMovies,
        'pagination' => $pagination
    ));
  }

  /**
   * @Route("/top-rated-movies/{page}", name="topratedMovies", defaults={"page" = 1})
   */
  public function topratedmoviesAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getTopRated(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'topratedMovies',
      'pages_count' => $TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Movie:topratedmovies.html.twig', array(
        'movies' => $TopRatedMovies,
        'pagination' => $pagination
    ));
  }

  /**
   * @Route("/now-playing-movies/{page}", name="nowplayingMovies", defaults={"page" = 1})
   */
  public function nowplayingmoviesAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getNowPlaying(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'nowplayingMovies',
      'pages_count' => $TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Movie:nowplayingmovies.html.twig', array(
        'movies' => $TopRatedMovies,
        'pagination' => $pagination
    ));
  }

  /**
   * @Route("/up-coming-movies/{page}", name="upcomingMovies", defaults={"page" = 1})
   */
  public function upcomingmoviesAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getUpcoming(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'upcomingMovies',
      'pages_count' => $TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Movie:upcomingmovies.html.twig', array(
        'movies' => $TopRatedMovies,
        'pagination' => $pagination
    ));
  }

  /**
   * @Route(
   *     "/movies/{id}-{slug}/",
   *     name="viewmovie"
   * )
   */
  public function viewAction($slug, $id) {
    $movie = $this->get('tmdb.movie_repository')->load($id);
    $crew = $movie->getCredits()->getCrew();
    $cast = $movie->getCredits()->getCast();
    $listMovies = $movie->getSimilar();
    $listImages = $movie->getImages();
    $castList = $cast;
    $crewList = array();
    foreach ($crew as $key => $value) {
      $crewList[$value->getJob()][] = $value;
    }
    return $this->render('AppBundle:Movie:view.html.twig', array('movie' => $movie,
        'crewList' => $crewList,
        'castList' => $castList,
        'listMovies' => $listMovies,
        'listImages' => $listImages
        )
    );
  }

}

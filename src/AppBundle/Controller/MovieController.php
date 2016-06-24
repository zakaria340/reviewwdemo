<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sunra\PhpSimple\HtmlDomParser;

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
    $imdbId = $movie->getImdbId();
//    
    $urlToParse = 'http://layarkaca21.tv/search/' . $imdbId;
    $dom = HtmlDomParser::file_get_html($urlToParse);
    $href = $dom->find('.entry-header h2 a', 0)->href;
    $href = str_replace('http://lk21.tv/', '', $href);
    $href = str_replace('/', '', $href);
//
    $urldirect720 = "http://layarkaca21.tv/movie/auth.php?movie=$href&size=720&server=0";
    $urldirect360 = "http://layarkaca21.tv/movie/auth.php?movie=$href&size=360&server=0";


 

    $em = $this->getDoctrine()->getManager();
    $itemEntity = $em->getRepository('AppBundle:Item')->findBy(array('idApi' => $id));

    $listUrls = [];
    if (!is_null($itemEntity)) {
      $listUrls = $em->getRepository('AppBundle:Urls')
          ->findBy(array('item' => $itemEntity));
    }
//    foreach($listUrls as $key => $value){
//      var_dump($key);
//      var_dump($value);die;
//    }
    $listUrlsVideo = array(
      array(
        'url' => $urldirect720,
        'name' => 'Server 1 Direct',
        'qualite' => '720',
        'id' => 1,
        'type' => 'direct'
      ),
      array(
        'url' => $urldirect360,
        'name' => 'Server 2 Direct',
        'qualite' => '360',
        'id' => 2,
        'type' => 'direct'
      )
    );
    
       /**
     * 
     */
        $title = str_replace(' ', '+',  $movie->getTitle());
    $urlToParse = 'http://www.seehd.club/?s=' . $title;
    $dom2 = HtmlDomParser::file_get_html($urlToParse);
    if($dom2->find('#content .article-helper h2 a', 0)){
          $href = $dom2->find('#content .article-helper h2 a', 0)->href;

    $dom = HtmlDomParser::file_get_html($href);
    $urlUpload = $dom->find('.entry-content iframe', 0)->src;

    $parse = parse_url($urlUpload);
    $listUrlsVideo[] = array(
        'url' => $urlUpload,
        'name' => 'Upload',
        'qualite' => 'HD',
        'id' => 3,
        'type' => 'iframe',
        'host'  => $parse['host']
      );
    }
    return $this->render('AppBundle:Movie:view.html.twig', array(
          'movie' => $movie,
          'crewList' => $crewList,
          'castList' => $castList,
          'listMovies' => $listMovies,
          'listImages' => $listImages,
          'listUrls' => $listUrlsVideo
            )
    );
  }

}

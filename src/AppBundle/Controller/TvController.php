<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TvController extends Controller {

  /**
   * @Route(
   *     "/tv-show/{page}",
   *     name="tvshow",
   *    defaults={"page" = 1},
   *     requirements={
   *         "page": "\d*"
   *     }
   * )
   */
  public function tvshowAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getTvApi()->getPopular(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'tvshow',
      'pages_count' => $TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:Tv:tvshow.html.twig', array(
        'movies' => $TopRatedMovies,
        'pagination' => $pagination
    ));
  }

  /**
   * @Route(
   *     "/tv-show/{id}-{slug}/",
   *     name="viewtvshow"
   * )
   */
  public function viewtvshowAction($slug, $id) {

    $movie = $this->get('tmdb.tv_repository')->load($id);
    $crew = $movie->getCredits()->getCrew();
    $castList = $movie->getCredits()->getCast();
    $listMovies = $movie->getSimilar();
    $listSeasons = $movie->getSeasons();
    $crewList = array();
    foreach ($crew as $key => $value) {
      $crewList[$value->getJob()][] = $value;
    }
    return $this->render('AppBundle:Tv:viewtvshow.html.twig', array('movie' => $movie,
        'crewList' => $crewList,
        'castList' => $castList,
        'listMovies' => $listMovies,
        'listSeasons' => $listSeasons
        )
    );
  }

  /**
   * @Route(
   *     "/tv-show/{id}-{slug}/season/{idseason}/",
   *     
   *     name="viewseasonshow"
   * )
   */
  public function viewseasonshowAction($slug, $id, $idseason) {
    $movie = $this->get('tmdb.tv_season_repository')->load($id, $idseason);
    $tvshow = $this->get('tmdb.tv_repository')->load($id);
    $listEpisodes = $movie->getEpisodes();
    $listImages = $movie->getImages();
    return $this->render('AppBundle:Tv:viewseasonshow.html.twig', array(
        'movie' => $movie,
        'tvshow' => $tvshow,
        'listEpisodes' => $listEpisodes,
        'listImages' => $listImages,
        'idTv' => $id
        )
    );
  }

  /**
   * @Route(
   *     "/tv-show/{id}-{slug}/season/{idseason}/episode/{idepisode}/",
   *     name="viewepisodeshow"
   * )
   */
  public function viewepisodeshowAction($slug, $id, $idseason, $idepisode) {
    $movie = $this->get('tmdb.tv_episode_repository')->load($id, $idseason, $idepisode);
    $listImages = $movie->getImages();
    $tvshow = $this->get('tmdb.tv_repository')->load($id);
    $season = $this->get('tmdb.tv_season_repository')->load($id, $idseason);

    return $this->render('AppBundle:Tv:viewepisodeshow.html.twig', array(
        'movie' => $movie,
        'tvshow' => $tvshow,
        'season' => $season,
        'listImages' => $listImages,
        )
    );
  }

}

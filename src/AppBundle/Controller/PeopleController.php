<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PeopleController extends Controller {

  /**
   * @Route(
   *     "/people/{page}",
   *     name="people",
   *    defaults={"page" = 1},
   *     requirements={
   *         "page": "\d*"
   *     }
   * )
   */
  public function peopleAction($page) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getPeopleApi()->getPopular(array('page' => $page));
    $pagination = array(
      'page' => $page,
      'route' => 'people',
      'pages_count' => 10,//$TopRatedMovies['total_pages'],
      'route_params' => array()
    );
    return $this->render('AppBundle:People:people.html.twig', array(
        'items' => $TopRatedMovies['results'],
        'pagination' => $pagination
    ));
  }

  /**
   * @Route(
   *     "/people/{id}-{slug}/",
   *     name="viewpeople"
   * )
   */
  public function viewpersonAction($slug, $id) {
    $people = $this->get('tmdb.people_repository')->load($id);
    $listImages = $people->getImages();
    $crewMovie = $people->getMovieCredits()->getCrew();
    $castMovie = $people->getMovieCredits()->getCast();

    $crewTv = $people->getTvCredits()->getCrew();
    $castTv = $people->getTvCredits()->getCast();

    return $this->render('AppBundle:People:viewpeople.html.twig', array(
        'people' => $people,
        'listImages' => $listImages,
        'listCrewMovie' => $crewMovie,
        'listCastMovie' => $castMovie,
        'listCrewTv' => $crewTv,
        'listCastTv' => $castTv,
        'idTv' => $id
        )
    );
  }

}

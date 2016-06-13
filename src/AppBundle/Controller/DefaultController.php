<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {
        $client = $this->get('tmdb.client');
        $TopRatedMovies = $client->getMoviesApi()->getPopular(array('page' => 1));
        $TopRatedMovies = $TopRatedMovies['results'];
        foreach ($TopRatedMovies as $key => $tv) {
            $TopRatedMovies[$key]['slug'] = $this->slug($tv['title'], '-');
        }
        $firstMovies = array_slice($TopRatedMovies, 0, 3);
        $movies = array_slice($TopRatedMovies, 3, 4);
        $TopRatedTV = $client->getTvApi()->getPopular(array('page' => 1));
        $TopRatedTV = $TopRatedTV['results'];
        foreach ($TopRatedTV as $key => $tv) {
            $TopRatedTV[$key]['slug'] = $this->slug($tv['name'], '-');
        }
        $firstTvs = array_slice($TopRatedTV, 0, 3);
        $tvs = array_slice($TopRatedTV, 3, 4);
        //var_dump($firstTvs);
        //first_air_datevar_dump($tvs);die;

        foreach ($firstTvs as $key => $tv) {
            $firstTvs[$key]['slug'] = $this->slug($tv['name'], '-');
        }
        return $this->render('AppBundle:Core:index.html.twig', array(
                    'firstMovies' => $firstMovies,
                    'firstTvs' => $firstTvs,
                    'movies' => $movies,
                    'tvs' => $tvs,
        ));
    }

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
        return $this->render('AppBundle:Core:tvshow.html.twig', array(
                    'movies' => $TopRatedMovies,
                    'pagination' => $pagination
        ));
    }

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
            'pages_count' => $TopRatedMovies['total_pages'],
            'route_params' => array()
        );
        return $this->render('AppBundle:Core:people.html.twig', array(
                    'items' => $TopRatedMovies['results'],
                    'pagination' => $pagination
        ));
    }
    
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
        return $this->render('AppBundle:Core:popularmovies.html.twig', array(
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
        return $this->render('AppBundle:Core:topratedmovies.html.twig', array(
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
        return $this->render('AppBundle:Core:nowplayingmovies.html.twig', array(
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
        return $this->render('AppBundle:Core:upcomingmovies.html.twig', array(
                    'movies' => $TopRatedMovies,
                    'pagination' => $pagination
        ));
    }

    /**
     * @Route(
     *     "/movies/{id}-{slug}.{_format}",
     *     
     *     name="viewmovie",
     *     requirements={
     *         "_format": "html"
     *     }
     * )
     */
    public function viewAction($slug, $id, $_format) {
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
        return $this->render('AppBundle:Core:view.html.twig', array('movie' => $movie,
                    'crewList' => $crewList,
                    'castList' => $castList,
                    'listMovies' => $listMovies,
                    'listImages' => $listImages
                        )
        );
    }

//    /**
//     * @Route("/movies/{page}", name="movies", defaults={"page" = 1})
//     */
//    public function moviesAction($page) {
//        $client = $this->get('tmdb.search_repository');
//        $query = new \Tmdb\Model\Search\SearchQuery\MovieSearchQuery();
//        $query->page($page);
//        $find = $client->searchMovie('batman', $query);
//
//        $pagination = array(
//            'page' => $page,
//            'route' => 'movies',
//            'pages_count' => $find['total_pages'],
//            'route_params' => array()
//        );
//        return $this->render('AppBundle:Core:movies.html.twig', array(
//                    'movies' => $find,
//                    'pagination' => $pagination
//        ));
//    }

    /**
     * @Route(
     *     "/tv-show/{id}-{slug}.{_format}",
     *     
     *     name="viewtvshow",
     *     requirements={
     *         "_format": "html"
     *     }
     * )
     */
    public function viewtvshowAction($slug, $id, $_format) {

        $movie = $this->get('tmdb.tv_repository')->load($id);
        $crew = $movie->getCredits()->getCrew();
        $castList = $movie->getCredits()->getCast();
        $listMovies = $movie->getSimilar();
        $listSeasons = $movie->getSeasons();
        $crewList = array();
        foreach ($crew as $key => $value) {
            $crewList[$value->getJob()][] = $value;
        }
        return $this->render('AppBundle:Core:viewtvshow.html.twig', array('movie' => $movie,
                    'crewList' => $crewList,
                    'castList' => $castList,
                    'listMovies' => $listMovies,
                    'listSeasons' => $listSeasons
                        )
        );
    }

    /**
     * @Route(
     *     "/tv-show/{id}-{slug}/season-{idseason}.{_format}",
     *     
     *     name="viewseasonshow",
     *     requirements={
     *         "_format": "html"
     *     }
     * )
     */
    public function viewseasonshowAction($slug, $id, $idseason, $_format) {
        $movie = $this->get('tmdb.tv_season_repository')->load($id, $idseason);
        $listEpisodes = $movie->getEpisodes();
        $listImages = $movie->getImages();
        return $this->render('AppBundle:Core:viewseasonshow.html.twig', array('movie' => $movie,
                    'listEpisodes' => $listEpisodes,
                    'listImages' => $listImages,
                    'idTv' => $id
                        )
        );
    }

    /**
     * @Route(
     *     "/tv-show/{id}-{slug}/season-{idseason}/episode-{idepisode}.{_format}",
     *     
     *     name="viewepisodeshow",
     *     requirements={
     *         "_format": "html"
     *     }
     * )
     */
    public function viewepisodeshowAction($slug, $id, $idseason, $idepisode, $_format) {
        $movie = $this->get('tmdb.tv_episode_repository')->load($id, $idseason, $idepisode);
        $listImages = $movie->getImages();
        //var_dump($movie);die;
        return $this->render('AppBundle:Core:viewepisodeshow.html.twig', array(
                    'movie' => $movie,
                    'listImages' => $listImages,
                        )
        );
    }

    /**
     * @Route(
     *     "/people/{id}-{slug}.{_format}",
     *     
     *     name="viewpeople",
     *     requirements={
     *         "_format": "html"
     *     }
     * )
     */
    public function viewpersonAction($slug, $id, $_format) {
        $people = $this->get('tmdb.people_repository')->load($id);
        $listImages = $people->getImages();
        $crewMovie = $people->getMovieCredits()->getCrew();
        $castMovie = $people->getMovieCredits()->getCast();

        $crewTv = $people->getTvCredits()->getCrew();
        $castTv = $people->getTvCredits()->getCast();

        return $this->render('AppBundle:Core:viewpeople.html.twig', array(
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

    public function slug($str, $char) {

        // Lower case the string and remove whitespace from the beginning or end
        $str = trim(strtolower($str));

        // Remove single quotes from the string
        $str = str_replace('', '', $str);

        // Every character other than a-z, 0-9 will be replaced with a single dash (-)
        $str = preg_replace('/[^a-z0-9]+/', $char, $str);

        // Remove any beginning or trailing dashes
        $str = trim($str, $char);

        return $str;
    }

}

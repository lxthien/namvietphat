<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\NewsCategory;
use AppBundle\Entity\News;
use AppBundle\Entity\Testimonial;

class HomepageController extends Controller
{
    public function indexAction(Request $request)
    {
        $listCategoriesOnHomepage = $this->get('settings_manager')->get('listCategoryOnHomepage');
        $blocksOnHomepage = array();

        if (!empty($listCategoriesOnHomepage)) {
            $listCategoriesOnHomepage = json_decode($listCategoriesOnHomepage, true);

            if (is_array($listCategoriesOnHomepage)) {
                for ($i = 0; $i < count($listCategoriesOnHomepage); $i++) {
                    $blockOnHomepage = [];
                    $category = $this->getDoctrine()
                                    ->getRepository(NewsCategory::class)
                                    ->find($listCategoriesOnHomepage[$i]["id"]);

                    if ($category) {
                        $listCategoriesIds = array($category->getId());
                        $listSubIds = explode(",", $listCategoriesOnHomepage[$i]["subId"]);
                        $listSubTabs = [];

                        if ($listCategoriesOnHomepage[$i]["subId"] != null && count($listSubIds) > 0) {
                            for ($j = 0; $j < count($listSubIds); $j++) {
                                $subCat = $this->getDoctrine()
                                        ->getRepository(NewsCategory::class)
                                        ->find($listSubIds[$j]);

                                if ($subCat) {
                                    $posts = $this->getDoctrine()
                                        ->getRepository(News::class)
                                        ->createQueryBuilder('n')
                                        ->innerJoin('n.category', 't')
                                        ->where('t.id =:subCat')
                                        ->andWhere('n.enable = :enable')
                                        ->setParameter('subCat', $subCat->getId())
                                        ->setParameter('enable', 1)
                                        ->orderBy('n.createdAt', 'DESC')
                                        ->getQuery()->getResult();
                                }

                                $listSubTabs[] = (object) array('subCategoryId' => $subCat->getId(), 'subCategoryName' => $subCat->getName(), 'posts' => $posts);
                            }
                        } else {
                            $posts = $this->getDoctrine()
                                ->getRepository(News::class)
                                ->createQueryBuilder('n')
                                ->innerJoin('n.category', 't')
                                ->where('t.id =:subCat')
                                ->andWhere('n.enable = :enable')
                                ->setParameter('subCat', $category->getId())
                                ->setParameter('enable', 1)
                                ->orderBy('n.createdAt', 'DESC')
                                ->getQuery()->getResult();
                        }
                    }

                    $blockOnHomepage = (object) array('category' => $category, 'listSubTabs' => $listSubTabs, 'posts' => $posts, 'description' => $listCategoriesOnHomepage[$i]["description"]);
                    $blocksOnHomepage[] = $blockOnHomepage;
                }
            }
        }

        return $this->render('homepage/index.html.twig', [
            'blocksOnHomepage' => $blocksOnHomepage,
            'showSlide' => true
        ]);
    }

    /**
     * Render list testimonial
     * @return Testimonial
     */
    public function listTestimonialAction($template = NULL)
    {
        $testimonial = $this->getDoctrine()
            ->getRepository(Testimonial::class)
            ->findAll();

        if (!$template) {
            return $this->render('testimonial/testimonial.html.twig', [
                'testimonial' => $testimonial,
            ]);
        } else {
            return $this->render($template, [
                'testimonial' => $testimonial
            ]);
        }
    }
}

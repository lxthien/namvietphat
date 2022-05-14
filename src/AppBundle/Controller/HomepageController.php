<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\NewsCategory;
use AppBundle\Entity\News;
use AppBundle\Entity\Testimonial;
use AppBundle\Entity\Banner;
use AppBundle\Entity\Contact;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

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
                                        ->leftJoin('n.category', 't')
                                        ->where('t.id =:subCat')
                                        ->andWhere('n.enable = :enable')
                                        ->setParameter('subCat', $subCat->getId())
                                        ->setParameter('enable', 1)
                                        ->orderBy('n.createdAt', 'DESC')
                                        ->setMaxResults( $listCategoriesOnHomepage[$i]["items"] )
                                        ->getQuery()->getResult();
                                }

                                $listSubTabs[] = (object) array('subCategory' => $subCat, 'posts' => $posts);
                            }
                        } else {
                            $posts = $this->getDoctrine()
                                ->getRepository(News::class)
                                ->createQueryBuilder('n')
                                ->leftJoin('n.category', 't')
                                ->where('t.id =:subCat')
                                ->andWhere('n.enable = :enable')
                                ->setParameter('subCat', $category->getId())
                                ->setParameter('enable', 1)
                                ->orderBy('n.createdAt', 'DESC')
                                ->setMaxResults( $listCategoriesOnHomepage[$i]["items"] )
                                ->getQuery()->getResult();
                        }
                    }

                    $blockOnHomepage = (object) array('category' => $category, 'listSubTabs' => $listSubTabs, 'posts' => $posts, 'description' => $listCategoriesOnHomepage[$i]["description"]);
                    $blocksOnHomepage[] = $blockOnHomepage;
                }
            }
        }

        $banners = $this->getDoctrine()
            ->getRepository(Banner::class)
            ->findBy(
                array('bannercategory' => 1),
                array('createdAt' => 'DESC')
            );

        return $this->render('homepage/index.html.twig', [
            'blocksOnHomepage' => $blocksOnHomepage,
            'banners' => $banners,
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

    public function formContactAction()
    {
        $contact = new Contact();
        
        $form = $this->createFormBuilder($contact)
            ->setAction($this->generateUrl('homepagecontact'))
            ->add('name', TextType::class, array('label' => 'Tên của bạn'))
            ->add('phone', TextType::class, array('label' => 'Số điện thoại'))
            ->add('contents', TextareaType::class, array(
                'required' => true,
                'label' => 'Nội dung',
                'attr' => array('rows' => '7')
            ))
            ->add('send', ButtonType::class, array('label' => 'Đăng ký'))
            ->getForm();

        return $this->render('layout/contactFooter.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/homepage-contact", name="homepagecontact")
     * 
     * @return JSON
     */
    public function handleContactFormAction(Request $request, \Swift_Mailer $mailer)
    {
        if (!$request->isXmlHttpRequest()) {
            return new Response(
                json_encode(
                    array(
                        'status'=>'error',
                        'message' => 'You can access this only using Ajax!'
                    )
                )
            );
        } else {
            $contact = new Contact();
            
            $form = $this->createFormBuilder($contact)
                ->add('name', TextType::class)
                ->add('phone', TextType::class)
                ->add('contents', TextareaType::class)
                ->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($contact);
                $em->flush();

                if (null !== $contact->getId()) {   
                    return new Response(
                        json_encode(
                            array(
                                'status'=>'success',
                                'message' => 'Chúng tôi đã nhận được thông tin của bạn. Chúng tôi sẽ kiểm tra và liên hệ sớm đến bạn!'
                            )
                        )
                    );
                } else {
                    return new Response(
                        json_encode(
                            array(
                                'status'=>'error',
                                'message' => 'Đã có lỗi xảy ra! Vui lòng thử lại sau'
                            )
                        )
                    );
                }
            } else {
                return new Response(
                    json_encode(
                        array(
                            'status'=>'error',
                            'message' => 'Đã có lỗi xảy ra! Vui lòng thử lại sau'
                        )
                    )
                );
            }
        }
    }
}

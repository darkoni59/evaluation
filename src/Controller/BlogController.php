<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    //cette fonction permet d'afficher les differents articles l'un apres l'autres
    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo)
    {


        $articles = $repo->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/",name="home")
     */
    public function home()
    {

        return $this->render('blog/home.html.twig', ['title' => "bienvenue ici les gens"]);

    }
//fonction importante pouvant servire à la fois de creation ainsi que d'éditer un article via son ID
    /**
     * @Route("admin/blog/new",name="blog_create")
     * @Route("admin/blog/{id}/edit",name="blog_edit")
     */
    public function form(Article $article = null, Request $request, ObjectManager $manager)
    {
        if (!$article) {
            $article = new Article();
        }


        $form = $this->createFormBuilder($article)
            ->add('title')
            ->add('content')
            ->add('image')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$article->getId()) {
                $article->setCreatedAt(new \DateTime());
            }
            $manager->persist($article);
            $manager->flush();
            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        return $this->render('blog/create.html.twig', ['formArticle' => $form->createView(), 'editMode' => $article->getId() !== null]);

    }
    //fonction permetant de voir les articles sur une page twig grace à route show
    /**
     * @Route("/blog/{id}",name="blog_show")
     */

    public function show(Article $article, Request $request, ObjectManager $manager)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTime())
                ->setArticle($article);
            $manager->persist($comment);
            $manager->flush();
            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);

        }
        return $this->render('blog/show.html.twig', ['article' => $article, 'commentForm' => $form->createView()]);


    }
//fonction de suppression de commentaires
    /**
     * @Route("/admin/{id}/suppcomment",name="blog_comment",methods={"DELETE"})
     */
    public function deletecom(Comment $comment=null,Request $request,ObjectManager $manager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($comment);

            $entityManager->flush();

        }
        return $this->redirectToRoute('blog');
    }

    /**
     * @Route("/admin/{id}/delete",name="blog_delete")
     */
    //fonction de suppression d'article
    public function delete(Article $article = null, Request $request, ObjectManager $manager)
    {

        $form = $this->createFormBuilder($article)
            ->add('title')
            ->add('content')
            ->add('image')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->remove($article);
            $manager->flush();
            return $this->redirectToRoute('blog', ['id' => $article->getId()]);
        }

        return $this->render('blog/delete.html.twig', ['formArticle' => $form->createView()]);

    }

//fonction d'identification de l'admin
    /**
     * @Route("/admin",name="admin")
     */
    public function base()
    {

        return $this->render('admin/index.html.twig', ['controller_name' => 'AdminController',]);
    }
}

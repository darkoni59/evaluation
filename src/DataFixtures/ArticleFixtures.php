<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Comment;
class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker=\Faker\Factory::create('fr_FR');

        for ($i=1;$i<=mt_rand(5,7);$i++){
            $article=new Article();

            $content='<p>'.join($faker->paragraphs(2),'</p><p>').'</p>';
            $article->setTitle($faker->sentence())
                ->setContent($content)
                ->setImage($faker->imageUrl())
                ->setCreatedAt($faker->dateTimeBetween('-6 months'));
            $manager->persist($article);
            for ($k=1;$k <= mt_rand(4,10);$k++){
                $comment=new Comment();


                $now=new \DateTime();
                $interval=$now->diff($article->getCreatedAt());
                $days=$interval->days;
                $minimum='-'.$days.'days';//-100 days

                $comment->setAuthor($faker->name)
                    ->setContent($content)
                    ->setCreatedAt($faker->dateTimeBetween($minimum))
                    ->setArticle($article);
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }
}

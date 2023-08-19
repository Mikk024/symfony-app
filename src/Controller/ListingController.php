<?php

namespace App\Controller;

use App\Entity\Listing;
use App\Form\ListingFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ListingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;

class ListingController extends AbstractController
{
    #[Route('/listing/create', 
    name: 'listing.create',
    )]

    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ValidatorInterface $validator): Response
    {
        $listing = new Listing();

        $form = $this->createForm(ListingFormType::class, $listing);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('filePath')->getData();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFilename);
                $newFileName = $safeFileName.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('logos_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                    throw $e;
                }

                $listing->setFilePath($newFileName);
            }

            $listing->setUser($this->getUser());

            $errors = $validator->validate($listing);
            $entityManager->persist($listing);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }
    
        return $this->render('listing/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/listing', 
    name: 'listing.index',
    )]
    public function index(ListingRepository $listingRepository)
    {
        $listings = $listingRepository->findAll();

        return $this->render('listing/index.html.twig', [
            'listings' => $listings
        ]);
    }

    #[Route('/listing/manage', 
    name: 'listing.manage',
    )]
    public function manage(UserInterface $user)
    {
        return $this->render('listing/manage.html.twig',[
            'listings' => $user->getListings()
        ]);
    }

    #[Route(
        '/listing/{id}',
        name: 'listing.show',
        methods: ['GET']
    )]
    public function show($id, ListingRepository $listingRepository)
    {
        $listing = $listingRepository
            ->find($id);

        return $this->render('listing/show.html.twig', [
            'listing' => $listing
        ]);
    }

    #[Route(
        '/listing/{id}',
        name: 'listing.delete',
        methods: ['POST']
    )]
    public function delete(Request $request, Listing $listing, EntityManagerInterface $entityManager)
    {
        if ($this->isCsrfTokenValid('delete'.$listing->getId(), $request->request->get('_token'))) {
            $entityManager->remove($listing);
            $entityManager->flush();
        }

        return $this->redirectToRoute('listing.manage');
    }

    #[Route(
        '/listing/{id}/edit',
        name: 'listing.edit',
        methods: ['GET', 'POST']
    )]
    public function edit(Request $request, Listing $listing, EntityManagerInterface $entityManager)
    {
       if ($listing->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException();
       }

        $form = $this->createForm(ListingFormType::class, $listing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('listing/edit.html.twig', [
            'listing' => $listing,
            'form' => $form
        ]);
    }

    #[Route(
        '/listings',
        name: 'listing.query'
    )]
    public function query(ListingRepository $listingRepository, Request $request, PaginatorInterface $paginator)
    {
        $form = $this->createFormBuilder()
            ->setMethod('GET')
            ->add('name', TextType::class, [
                'required' => false
            ])
            ->add('search', SubmitType::class)
            ->getForm()
        ;

        $listings = $listingRepository->findAll();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $listings = $listingRepository->search($data['name']);
        }

        $pagination = $paginator->paginate(
            $listings,
            $request->query->getInt('page', 1),
            2
        );

        return $this->render('listing/query.html.twig', [
            'listings' => $pagination,
            'form' => $form
        ]);
    }

}

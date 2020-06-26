<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\User;
use App\Form\BookType;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;


/**
 * @Route("/book")
 */
class BookController extends AbstractController
{
    /**
     * @Route("/", name="book_index", methods={"GET"})
     */
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findByBook(),
            'user' => null,
        ]);
    }

    /**
     * @Route("/my", name="book_my", methods={"GET"})
     */
    public function indexMy(BookRepository $bookRepository): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('app_login');
        }
        $user = new User();
        $user = $this->getUser();
        $books = new Book();
        $books = $this->getDoctrine()
            ->getRepository(Book::class)
            ->findByIdBooks($user);
        return $this->render('book/index.html.twig', [
            'books' => $books,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/new", name="book_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('app_login');
        }
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = new User();
            $user = $this->getUser();
            $book->setUsers($user);

            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form->get('coverPct')->getData();
            if ($file) {
                // Generate a unique name for the file before saving it
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                // Move the file to the directory where user avatars are stored
                $file->move(
                // This parameter should be configured
                    $this->getParameter('image_directory'),
                    $fileName
                );
                // instead of its contents
                $book->setCoverPct($fileName);
            }

            $file = $form->get('fileBook')->getData();
            if ($file) {
                // Generate a unique name for the file before saving it
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                // Move the file to the directory where user avatars are stored
                $file->move(
                // This parameter should be configured
                    $this->getParameter('file_directory'),
                    $fileName
                );
                // instead of its contents
                $book->setFileBook($fileName);
            }

            $book->setDateRead();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="book_show", methods={"GET"})
     */
    /*    public function show(Book $book): Response
        {
            return $this->render('book/show.html.twig', [
                'book' => $book,
            ]);
        }*/

    /**
     * @Route("/{id}/edit", name="book_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Book $book): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('app_login');
        }
        $filesystem = new Filesystem();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form->get('coverPct')->getData();
            if ($file) {
                $filesystem->remove('uploads/image/' . $book->getCoverPct());
                // Generate a unique name for the file before saving it
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                // Move the file to the directory where user avatars are stored
                $file->move(
                // This parameter should be configured
                    $this->getParameter('image_directory'),
                    $fileName
                );
                // instead of its contents
                $book->setCoverPct($fileName);
            }

            $file = $form->get('fileBook')->getData();
            if ($file) {
                $filesystem->remove('uploads/file/' . $book->getFileBook());
                // Generate a unique name for the file before saving it
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                // Move the file to the directory where user avatars are stored
                $file->move(
                // This parameter should be configured
                    $this->getParameter('file_directory'),
                    $fileName
                );
                // instead of its contents
                $book->setFileBook($fileName);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirect('/book/' . $book->getId() . '/edit');
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="book_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Book $book): Response
    {
        if ($this->isCsrfTokenValid('delete' . $book->getId(), $request->request->get('_token'))) {
            $filesystem = new Filesystem();
            $filesystem->remove('uploads/file/' . $book->getFileBook());
            $filesystem->remove('uploads/image/' . $book->getCoverPct());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('book_my');

    }

    /**
     * @Route("/{id}", name="image_delete", methods={"DELETEIMG"})
     */
    public function deleteImg(Request $request, Book $book): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($this->isCsrfTokenValid('deleteImg' . $book->getId(), $request->request->get('_token'))) {
            $path = 'uploads/image/' . $book->getCoverPct();
            unlink($path);
            $book->setCoverPct('');
            $this->getDoctrine()->getManager()->flush();
        }
        return $this->redirect('/book/' . $book->getId() . '/edit');
    }

    /**
     * @Route("/{id}", name="file_delete", methods={"DELETEFILE"})
     */
    public function deleteFile(Request $request, Book $book): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($this->isCsrfTokenValid('deleteFile' . $book->getId(), $request->request->get('_token'))) {
            $path = 'uploads/file/' . $book->getFileBook();
            unlink($path);
            $book->setFileBook('');
            $this->getDoctrine()->getManager()->flush();
        }
        return $this->redirect('/book/' . $book->getId() . '/edit');
    }
}

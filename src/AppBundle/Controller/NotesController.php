<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Notes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\NoteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\Mapping;

class NotesController extends Controller {

    /**
     * @Route("/list/{page}", defaults={"page" = 1}, name="notes_list")
     */
    public function listAction($page, Request $request) {
        $allNotes = $this->getUserNotesAction();
        $this->get('session')->set('numOfNotes', count($allNotes));

        $onePageNotes = $this->get('knp_paginator')
            ->paginate($allNotes, $request->query->get('page', $page/*page number*/)
        );

        if (count($onePageNotes) == 0 && $page > 1) {
            $previousPage = $page - 1;
            $url = $this->generateUrl(
                'notes_list',
                array('page' => $previousPage)
                );

            return $this->redirect($url);
        }        
        return $this->render('notes/index.html.twig', array(
            'notes' => $onePageNotes
        ));      
    }

    /**
     * @Route("/create", name="create_note")
     */
    public function createAction(Request $request) {
        $note = new Notes();

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userId = $this->getUser()->getId();
            $currentTime = new\DateTime('now');

            $note->setUserId($userId);
            $note->setCreateDate($currentTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($note);
            $em->flush();

            return $this->redirectToRoute('notes_list');
        }

        return $this->render('notes/create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/edit/{id}", name="edit_note")
     */
    public function editAction($id, Request $request) {
        $note = $this->getDoctrine()
            ->getRepository('AppBundle:Notes')
            ->find($id);

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userId = $this->getUser()->getId();
            $currentTime = new\DateTime('now');

            $note->setUserId($userId);
            $note->setCreateDate($currentTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($note);
            $em->flush();

            return $this->redirectToRoute('note_details', array('id' => $id));
        }

        return $this->render('notes/edit.html.twig', array(
            'note' => $note,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/details/{id}", name="note_details")
     */
    public function noteDetailsAction($id) {
        $note = $this->getDoctrine()
            ->getRepository('AppBundle:Notes')
            ->find($id);

        return $this->render('notes/details.html.twig', array(
            'note' => $note
        ));
    }

    /**
     * @Route("/delete/{id}", name="delete_note")
     */
    public function deleteNoteAction($id, Request $request) {        
        $note = $this->getDoctrine()
            ->getRepository('AppBundle:Notes')
            ->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($note);
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    private function getUserNotesAction() {
        $userId = $this->getUser()->getId();

        $notes = $this->getDoctrine()
            ->getRepository('AppBundle:Notes')
            ->findByUserId(array('userId' => $userId),
                array('id' => 'DESC')
            );
        return $notes;
    }
}
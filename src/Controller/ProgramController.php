<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\ProgramType;
use App\Repository\ProgramRepository;
use App\Service\Slugify;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/program', name: 'program_')]
class ProgramController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $programs = $doctrine->getRepository(Program::class)->findAll();

        return $this->render('program/index.html.twig', [
            'programs' => $programs,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, ProgramRepository $programRepository, Slugify $slugify, MailerInterface $mailer): Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $programRepository->add($program);
            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('jonathan.poitrenaud@hotmail.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('email/newProgramEmail.html.twig', ['program' => $program]));
            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                echo 'there is a mail error';
                die();
            }

            return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('program/new.html.twig', [
            'program'   => $program,
            'form'      => $form,
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, Program $program): Response
    {
        $seasons = $doctrine->getRepository(Season::class)->findByProgram($program);

        if (!$program) {
            throw $this->createNotFoundException(
              'No program with title : ' .$program->getTitle(). ' found in this program\'s table.'
            );
        }

        return $this->render('program/show.html.twig', [
            'program'   => $program,
            'seasons'   => $seasons,
        ]);
    }

    #[Route('{slug}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Program $program, ProgramRepository $programRepository): Response
    {
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $programRepository->add($program);
            return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('program/edit.html.twig', [
            'program'   => $program,
            'form'      => $form,
        ]);
    }

    #[Route('delete/{id}', name: 'delete', methods: ['POST'])]
    public function deleteProgram(Request $request, Program $program, ProgramRepository $programRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->request->get('_token'))) {
            $programRepository->remove($program);
        }

        return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{slug}/season/{season}', name: 'season_show')]
    public function showSeason(Program $program, Season $season, ManagerRegistry $doctrine): Response
    {
        $episodes = $doctrine->getRepository(Episode::class)->findBySeason($season);

        return $this->render('program/season_show.html.twig', [
            'program'   => $program,
            'season'    => $season,
            'episodes'  => $episodes,
        ]);
    }

    #[Route('/{slug}/season/{season}/episode/{episode}', name: 'episode_show')]
    public function showEpisodes(Program $program, Season $season, Episode $episode): Response
    {
        return $this->render('program/episode_show.html.twig', [
            'program'   => $program,
            'season'    => $season,
            'episode'   => $episode,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\ProgramType;
use App\Repository\ProgramRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function new(Request $request, ProgramRepository $programRepository)
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $programRepository->add($program);
            $this->addFlash('success', 'You add a new program !');

            return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('program/new.html.twig', [
            'program'   => $program,
            'form'      => $form,
        ]);
    }

    #[Route('/{program}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, Program $program): Response
    {
        $seasons = $doctrine->getRepository(Season::class)->findByProgram($program);

        if (!$program) {
            throw $this->createNotFoundException(
              'No program with id : ' .$program->getTitle(). ' found in this program\'s table.'
            );
        }

        return $this->render('program/show.html.twig', [
            'program'   => $program,
            'seasons'   => $seasons,
        ]);
    }

    #[Route('/{program}/season/{season}', name: 'season_show')]
    public function showSeason(Program $program, Season $season, ManagerRegistry $doctrine): Response
    {
        $episodes = $doctrine->getRepository(Episode::class)->findBySeason($season);

        return $this->render('program/season_show.html.twig', [
            'program'   => $program,
            'season'    => $season,
            'episodes'  => $episodes,
        ]);
    }

    #[Route('/{program}/season/{season}/episode/{episode}', name: 'episode_show')]
    public function showEpisodes(Program $program, Season $season, Episode $episode, ManagerRegistry $doctrine): Response
    {
        return $this->render('program/episode_show.html.twig', [
            'program'   => $program,
            'season'    => $season,
            'episode'   => $episode,
        ]);
    }
}

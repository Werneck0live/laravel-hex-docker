<?php

namespace App\Application\UseCases;

use App\Domain\Contracts\SkillRepository;
use App\Domain\Skill;

final class ListSkills
{
    public function __construct(private readonly SkillRepository $repo) {}

    /** @return Skill[] */
    public function handle(): array
    {
        return $this->repo->all();
    }
}

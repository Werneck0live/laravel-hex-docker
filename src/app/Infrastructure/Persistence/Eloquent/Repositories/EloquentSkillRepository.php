<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\SkillRepository;
use App\Domain\Skill;
use App\Infrastructure\Persistence\Eloquent\Models\SkillModel;

class EloquentSkillRepository implements SkillRepository
{
    /** @return Skill[] */
    public function all(): array
    {
        return SkillModel::query()
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn ($m) => new Skill(
                id: (int) $m->id,
                name: (string) $m->name,
                level: (int) $m->level,
                yearsExperience: (int) $m->years_experience,
                tags: (array) ($m->tags ?? []),
                statusId: (int) $m->status_id,
            ))
            ->all();
    }
}

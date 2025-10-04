<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\SkillWriter;
use App\Domain\Skill;
use App\Infrastructure\Persistence\Eloquent\Models\SkillModel;

class EloquentSkillWriter implements SkillWriter
{
    public function create(array $payload): Skill
    {
        $model = SkillModel::query()->create([
            'name'              => $payload['name'],
            'level'             => $payload['level'],
            'years_experience'  => $payload['years_experience'],
            'tags'              => $payload['tags'] ?? [],
            'status_id'         => $payload['status_id'] ?? 1,
        ]);

        return new Skill(
            id: (int) $model->id,
            name: (string) $model->name,
            level: (int) $model->level,
            yearsExperience: (int) $model->years_experience,
            tags: (array) ($model->tags ?? []),
            statusId: (int) $model->status_id,
        );
    }
}

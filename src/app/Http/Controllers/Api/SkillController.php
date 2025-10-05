<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\ListSkills;
use App\Application\UseCases\CreateSkill;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index(ListSkills $uc): JsonResponse
    {
        $skills = $uc->handle();

        return response()->json([
            'data' => array_map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'level' => $s->level,
                'years_experience' => $s->yearsExperience,
                'tags' => $s->tags,
            ], $skills)
        ]);
    }

    public function store(Request $request, CreateSkill $uc): JsonResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:120',
            'level'             => 'required|integer|min:1|max:5',
            'years_experience'  => 'required|integer|min:0|max:60',
            'tags'              => 'array',
            'tags.*'            => 'string',
            'status_id'         => 'integer|min:0|max:2',
        ]);

        $skill = $uc->handle($data);

        return response()->json([
            'data' => [
                'id' => $skill->id,
                'name' => $skill->name,
                'level' => $skill->level,
                'years_experience' => $skill->yearsExperience,
                'tags' => $skill->tags,
            ]
        ], 201);
    }
}

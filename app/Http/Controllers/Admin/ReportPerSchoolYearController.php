<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Classes;
use App\Models\TeacherClassAssignment;
use App\Models\StudentContentProgress;
use App\Models\PreAssessment;
use App\Models\PostAssessment;
use App\Models\InterventionMaterial;
use App\Models\InterventionVideo;
use App\Models\InterventionQuiz;
use App\Models\ContentItem;
use Illuminate\Http\Request;

class ReportPerSchoolYearController extends Controller
{
    public function index(Request $request)
    {
        $schoolYears = SchoolYear::orderByDesc('year')->get();

        $selectedYear = null;
        $gradeLevels = [];

        $selectedYearId = $request->input('school_year_id');
        if ($selectedYearId) {
            $selectedYear = SchoolYear::find($selectedYearId);
        } else {
            $selectedYear = SchoolYear::where('is_active', true)->first()
                ?? $schoolYears->first();
        }

        if ($selectedYear) {
            $gradeLevels = $this->buildReport($selectedYear);
        }

        return view('admin.report', compact(
            'schoolYears',
            'selectedYear',
            'gradeLevels'
        ));
    }

    /**
     * Download a single class + subject as CSV (opens in Excel).
     * Expects: school_year_id, class_id, subject_id
     */
    public function downloadClassSubject(Request $request)
    {
        $request->validate([
            'school_year_id' => 'required|exists:school_years,id',
            'class_id'       => 'required|exists:classes,id',
            'subject_id'     => 'required|exists:subjects,id',
        ]);

        $schoolYear = SchoolYear::findOrFail($request->school_year_id);
        $class      = Classes::with(['studentClassRecords.studentProfile'])
            ->findOrFail($request->class_id);
        $subjectId  = (int) $request->subject_id;

        // The teacher assignment for this exact class + subject
        $assignment = TeacherClassAssignment::with(['teacherProfile.user', 'subject'])
            ->where('class_id', $class->id)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$assignment || !$assignment->subject) {
            return back()->with('error', 'No teacher assigned to this class and subject.');
        }

        $subject = $assignment->subject;

        // ---- Progress records for every student in this class ----
        $studentIds = $class->studentClassRecords
            ->pluck('student_profile_id')
            ->filter()
            ->values()
            ->toArray();

        $progressByStudent = collect();
        if (!empty($studentIds)) {
            $progressByStudent = StudentContentProgress::whereIn('student_profile_id', $studentIds)
                ->get()
                ->groupBy('student_profile_id');
        }

        // ---- Tag records with their subject_id ----
        $contentSubjectMaps = [
            'App\\Models\\ContentItem'          => ContentItem::pluck('subject_id', 'id')->toArray(),
            'App\\Models\\PreAssessment'        => PreAssessment::pluck('subject_id', 'id')->toArray(),
            'App\\Models\\PostAssessment'       => PostAssessment::pluck('subject_id', 'id')->toArray(),
            'App\\Models\\InterventionMaterial' => InterventionMaterial::pluck('subject_id', 'id')->toArray(),
            'App\\Models\\InterventionVideo'    => InterventionVideo::pluck('subject_id', 'id')->toArray(),
            'App\\Models\\InterventionQuiz'     => InterventionQuiz::pluck('subject_id', 'id')->toArray(),
        ];

        $filteredProgress = [];
        foreach ($progressByStudent as $studentId => $records) {
            foreach ($records as $record) {
                $map = $contentSubjectMaps[$record->content_type] ?? [];
                $subjectOfRecord = $map[$record->content_id] ?? null;
                if ($subjectOfRecord !== $subjectId) continue;
                $filteredProgress[$studentId][] = $record;
            }
        }
        foreach ($filteredProgress as $sid => $rows) {
            $filteredProgress[$sid] = collect($rows);
        }

        // ---- Totals for this grade level + subject ----
        $totals = $this->getSubjectTotals($class->grade_level, $subjectId);

        // ---- Build rows ----
        $rows = [];
        foreach ($class->studentClassRecords as $record) {
            $profile = $record->studentProfile;
            if (!$profile) continue;

            $fullName = trim(
                ($profile->first_name ?? '') . ' ' .
                ($profile->middle_name ? $profile->middle_name . ' ' : '') .
                ($profile->last_name ?? '') .
                ($profile->suffix_name ? ' ' . $profile->suffix_name : '')
            ) ?: 'Unknown';

            $records = collect($filteredProgress[$profile->id] ?? []);

            $rows[] = [
                'name' => $fullName,
                'lrn'  => $profile->lrn ?? 'N/A',
                'materials'   => $this->aggregateProgress(
                    $records,
                    ['App\\Models\\ContentItem'],
                    $totals['materials']
                ),
                'pre'  => $this->aggregateAssessment(
                    $records, 'App\\Models\\PreAssessment', $totals['pre']
                ),
                'post' => $this->aggregateAssessment(
                    $records, 'App\\Models\\PostAssessment', $totals['post']
                ),
                'interventions' => $this->aggregateProgress(
                    $records,
                    ['App\\Models\\InterventionMaterial', 'App\\Models\\InterventionVideo'],
                    $totals['interventions']
                ),
                'quiz' => $this->aggregateAssessment(
                    $records, 'App\\Models\\InterventionQuiz', $totals['quiz']
                ),
            ];
        }

        // ---- Stream CSV ----
        $teacherName = $this->resolveTeacherName($assignment);
        $safeSection = preg_replace('/[^A-Za-z0-9_-]/', '_', $class->section_name);
        $safeSubject = preg_replace('/[^A-Za-z0-9_-]/', '_', $subject->name);
        $filename = 'report_' . str_replace('-', '_', $schoolYear->year)
            . '_Grade' . $class->grade_level
            . '_' . $safeSection
            . '_' . $safeSubject
            . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows, $schoolYear, $class, $subject, $teacherName, $totals) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($out, ['Report — Class & Subject']);
            fputcsv($out, ['School Year: ' . $schoolYear->year]);
            fputcsv($out, ['Grade Level: ' . $class->grade_level]);
            fputcsv($out, ['Class: ' . $class->section_name]);
            fputcsv($out, ['Subject: ' . $subject->name]);
            fputcsv($out, ['Teacher: ' . $teacherName]);
            fputcsv($out, ['Generated: ' . now()->format('M d, Y H:i')]);
            fputcsv($out, []);

            // Totals summary
            fputcsv($out, ['Content Totals']);
            fputcsv($out, ['Materials', $totals['materials']]);
            fputcsv($out, ['Pre-Assessments', $totals['pre']]);
            fputcsv($out, ['Post-Assessments', $totals['post']]);
            fputcsv($out, ['Interventions', $totals['interventions']]);
            fputcsv($out, ['Mini Quizzes', $totals['quiz']]);
            fputcsv($out, []);

            // Student table
            fputcsv($out, [
                'Student', 'LRN',
                'Materials Completed', 'Materials Total', 'Materials %',
                'Pre-Test', 'Post-Test',
                'Interventions Completed', 'Interventions Total', 'Interventions %',
                'Mini Quiz',
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['name'],
                    $r['lrn'],
                    $r['materials']['completed'],
                    $r['materials']['total'],
                    $r['materials']['percent'] . '%',
                    $r['pre']['score']  !== null ? $r['pre']['score'] . '%'  : '—',
                    $r['post']['score'] !== null ? $r['post']['score'] . '%' : '—',
                    $r['interventions']['completed'],
                    $r['interventions']['total'],
                    $r['interventions']['percent'] . '%',
                    $r['quiz']['score'] !== null ? $r['quiz']['score'] . '%' : '—',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build the report: grade → class → subject → students.
     */
    private function buildReport(SchoolYear $schoolYear): array
    {
        $classes = Classes::with(['studentClassRecords.studentProfile'])
            ->where('school_year_id', $schoolYear->id)
            ->orderBy('grade_level')
            ->orderBy('section_name')
            ->get();

        if ($classes->isEmpty()) {
            return [];
        }

        $classIds = $classes->pluck('id')->toArray();

        $assignmentsByClass = TeacherClassAssignment::with(['teacherProfile.user', 'subject'])
            ->whereIn('class_id', $classIds)
            ->get()
            ->groupBy('class_id');

        $studentIds = $classes
            ->flatMap(fn ($c) => $c->studentClassRecords->pluck('student_profile_id'))
            ->unique()
            ->values()
            ->toArray();

        $progressRecords = collect();
        if (!empty($studentIds)) {
            $progressRecords = StudentContentProgress::whereIn('student_profile_id', $studentIds)->get();
        }

        $contentSubjectMaps = [
            'App\\Models\\ContentItem'          => ContentItem::pluck('subject_id', 'id')->toArray(),
            'App\\Models\\PreAssessment'        => PreAssessment::pluck('subject_id', 'id')->toArray(),
            'App\\Models\\PostAssessment'       => PostAssessment::pluck('subject_id', 'id')->toArray(),
            'App\\Models\\InterventionMaterial' => InterventionMaterial::pluck('subject_id', 'id')->toArray(),
            'App\\Models\\InterventionVideo'    => InterventionVideo::pluck('subject_id', 'id')->toArray(),
            'App\\Models\\InterventionQuiz'     => InterventionQuiz::pluck('subject_id', 'id')->toArray(),
        ];

        $progressByStudentSubject = [];
        foreach ($progressRecords as $record) {
            $map = $contentSubjectMaps[$record->content_type] ?? [];
            $subjectId = $map[$record->content_id] ?? null;
            if (!$subjectId) continue;
            $progressByStudentSubject[$record->student_profile_id][$subjectId][] = $record;
        }
        foreach ($progressByStudentSubject as $sid => $subjects) {
            foreach ($subjects as $subId => $rows) {
                $progressByStudentSubject[$sid][$subId] = collect($rows);
            }
        }

        $subjectTotalsCache = [];
        $result = [];

        foreach ($classes->groupBy('grade_level') as $gradeLevel => $classesInGrade) {
            $classesData = [];

            foreach ($classesInGrade as $class) {
                $classStudents = $class->studentClassRecords
                    ->map(fn ($r) => $r->studentProfile)
                    ->filter()
                    ->values();

                $subjectsData = [];

                foreach ($assignmentsByClass->get($class->id, collect()) as $assignment) {
                    $subject = $assignment->subject;
                    if (!$subject) continue;

                    $cacheKey = $gradeLevel . '|' . $subject->id;
                    if (!isset($subjectTotalsCache[$cacheKey])) {
                        $subjectTotalsCache[$cacheKey] = $this->getSubjectTotals($gradeLevel, $subject->id);
                    }
                    $totals = $subjectTotalsCache[$cacheKey];

                    $studentRows = [];
                    foreach ($classStudents as $profile) {
                        $records = collect($progressByStudentSubject[$profile->id][$subject->id] ?? []);

                        $fullName = trim(
                            ($profile->first_name ?? '') . ' ' .
                            ($profile->middle_name ? $profile->middle_name . ' ' : '') .
                            ($profile->last_name ?? '') .
                            ($profile->suffix_name ? ' ' . $profile->suffix_name : '')
                        ) ?: 'Unknown';

                        $studentRows[] = [
                            'id'          => $profile->id,
                            'name'        => $fullName,
                            'lrn'         => $profile->lrn ?? 'N/A',
                            'materials'   => $this->aggregateProgress(
                                $records,
                                ['App\\Models\\ContentItem'],
                                $totals['materials']
                            ),
                            'pre'         => $this->aggregateAssessment(
                                $records,
                                'App\\Models\\PreAssessment',
                                $totals['pre']
                            ),
                            'post'        => $this->aggregateAssessment(
                                $records,
                                'App\\Models\\PostAssessment',
                                $totals['post']
                            ),
                            'interventions' => $this->aggregateProgress(
                                $records,
                                ['App\\Models\\InterventionMaterial', 'App\\Models\\InterventionVideo'],
                                $totals['interventions']
                            ),
                            'quiz'        => $this->aggregateAssessment(
                                $records,
                                'App\\Models\\InterventionQuiz',
                                $totals['quiz']
                            ),
                        ];
                    }

                    $subjectsData[] = [
                        'subject'        => $subject->name,
                        'subject_id'     => $subject->id,
                        'teacher'        => $this->resolveTeacherName($assignment),
                        'employee_id'    => $assignment->teacherProfile->employee_id ?? '—',
                        'students'       => $studentRows,
                        'total_students' => count($studentRows),
                    ];
                }

                $classesData[] = [
                    'id'           => $class->id,
                    'section_name' => $class->section_name,
                    'subjects'     => $subjectsData,
                ];
            }

            $result[] = [
                'grade_level' => $gradeLevel,
                'classes'     => $classesData,
            ];
        }

        return $result;
    }

    private function getSubjectTotals($gradeLevel, $subjectId): array
    {
        return [
            'materials' => ContentItem::where('grade_level', $gradeLevel)
                ->where('subject_id', $subjectId)->count(),

            'pre' => PreAssessment::where('grade_level', $gradeLevel)
                ->where('subject_id', $subjectId)->count(),

            'post' => PostAssessment::where('grade_level', $gradeLevel)
                ->where('subject_id', $subjectId)->count(),

            'interventions' =>
                InterventionMaterial::where('grade_level', $gradeLevel)
                    ->where('subject_id', $subjectId)->count()
                + InterventionVideo::where('grade_level', $gradeLevel)
                    ->where('subject_id', $subjectId)->count(),

            'quiz' => InterventionQuiz::where('grade_level', $gradeLevel)
                ->where('subject_id', $subjectId)->count(),
        ];
    }

    private function resolveTeacherName($assignment): string
    {
        $profile = $assignment->teacherProfile;
        $user    = $profile?->user;

        $name = $user?->name;
        if (empty($name) && $profile) {
            $parts = array_filter([
                $profile->first_name  ?? null,
                $profile->middle_name ?? null,
                $profile->last_name   ?? null,
                $profile->suffix_name ?? null,
            ]);
            if (!empty($parts)) {
                $name = trim(implode(' ', $parts));
            }
        }
        if (empty($name)) {
            $name = $profile?->employee_id
                ? 'Teacher #' . $profile->employee_id
                : 'Unknown Teacher';
        }

        return $name;
    }

    private function aggregateProgress($records, array $contentTypes, int $total): array
    {
        $completed = $records
            ->whereIn('content_type', $contentTypes)
            ->whereIn('status', ['viewed', 'completed'])
            ->count();

        return [
            'completed' => $completed,
            'total'     => $total,
            'percent'   => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    private function aggregateAssessment($records, string $contentType, int $total): array
    {
        $taken = $records
            ->whereIn('content_type', $contentType)
            ->whereIn('status', ['submitted', 'completed'])
            ->count();

        $latest = $records
            ->where('content_type', $contentType)
            ->sortByDesc('created_at')
            ->first();

        return [
            'taken'   => $taken,
            'total'   => $total,
            'score'   => $latest->score ?? null,
            'percent' => $total > 0 ? (int) round(($taken / $total) * 100) : 0,
        ];
    }
}
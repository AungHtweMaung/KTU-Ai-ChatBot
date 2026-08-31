<?php

namespace Database\Seeders;

use App\Models\CurriculumSubject;
use App\Models\Department;
use App\Models\Major;
use App\Models\MajorYear;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds academic data (departments, majors, teachers, subjects, curriculum and
 * teacher assignments) transcribed from the university Google Sheet.
 *
 * Source sheet has full detail only for the Civil and CEIT departments; the
 * remaining departments are created as name-only stubs. The sheet has no
 * teacher emails, so placeholder emails are generated (@ktu.edu.mm). Teachers
 * referenced in a subject but absent from a department roster (foundation/minor
 * teachers) are created on demand and attached to the department of the first
 * curriculum that references them.
 *
 * Idempotent: safe to run repeatedly.
 */
class AcademicDataSeeder extends Seeder
{
    private const SCHOOL_YEAR = '2026-2027';

    /** Canonicalise spelling variants found in the source sheet. */
    private const TEACHER_ALIASES = [
        'Dr. Khain Myat Mon' => 'Dr. Khaing Myat Mon',
        'Daw Sandar Khiang' => 'Daw Sandar Khaing',
        'Daw Aye Thu Zar Soe' => 'Daw Aye Thuzar Soe',
    ];

    /** Cache of resolved Teacher models keyed by canonical name. */
    private array $teacherCache = [];

    public function run(): void
    {
        $departments = $this->seedDepartments();

        $this->seedDepartmentDetail(
            $departments['Civil'],
            $this->civilTeachers(),
            $this->civilCurriculum(),
        );

        $this->seedDepartmentDetail(
            $departments['CEIT'],
            $this->ceitTeachers(),
            $this->ceitCurriculum(),
        );
    }

    /**
     * Create the 9 departments and one same-named major per department.
     *
     * @return array<string, Department> keyed by department code
     */
    private function seedDepartments(): array
    {
        $rows = [
            ['name' => 'Civil Engineering', 'code' => 'Civil'],
            ['name' => 'Computer Engineering and Information Technology', 'code' => 'CEIT'],
            ['name' => 'Electrical Power', 'code' => 'EP'],
            ['name' => 'Electronic Engineering', 'code' => 'EC'],
            ['name' => 'Mechanical Engineering', 'code' => 'ME'],
            ['name' => 'Mechatronic Engineering', 'code' => 'MC'],
            ['name' => 'Metallurgy Engineering and Materials Science', 'code' => 'Met'],
            ['name' => 'Nuclear Technology', 'code' => 'NT'],
            ['name' => 'Biotechnology', 'code' => 'BioT'],
        ];

        $departments = [];

        foreach ($rows as $row) {
            $department = Department::updateOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name']],
            );

            // One major per department, mirroring the sheet's "Major" list.
            Major::updateOrCreate(
                ['department_id' => $department->id, 'name' => $row['name']],
                [],
            );

            $departments[$row['code']] = $department;
        }

        return $departments;
    }

    /**
     * Seed the roster, curriculum, subjects and assignments for one department.
     *
     * @param  array<int, array{name: string, position?: string}>  $teachers
     * @param  array<int, array{year: int, name: string, subjects: array}>  $curriculum
     */
    private function seedDepartmentDetail(Department $department, array $teachers, array $curriculum): void
    {
        $major = $department->majors()->firstOrFail();

        // Department roster teachers.
        foreach ($teachers as $teacher) {
            $this->resolveTeacher($teacher['name'], $department->id, $teacher['position'] ?? null);
        }

        foreach ($curriculum as $yearRow) {
            $majorYear = MajorYear::updateOrCreate(
                ['major_id' => $major->id, 'year_number' => $yearRow['year']],
                ['name' => $yearRow['name']],
            );

            foreach ($yearRow['subjects'] as $subjectRow) {
                $semester = $subjectRow['semester'] ?? 1;

                $subject = Subject::updateOrCreate(
                    ['code' => $subjectRow['code']],
                    ['name' => $subjectRow['name']],
                );

                $curriculumSubject = CurriculumSubject::updateOrCreate([
                    'major_id' => $major->id,
                    'major_year_id' => $majorYear->id,
                    'subject_id' => $subject->id,
                    'semester' => $semester,
                ], []);

                foreach ($subjectRow['teachers'] ?? [] as $teacherName) {
                    $teacher = $this->resolveTeacher($teacherName, $department->id);

                    TeacherAssignment::updateOrCreate([
                        'teacher_id' => $teacher->id,
                        'curriculum_subject_id' => $curriculumSubject->id,
                        'school_year' => self::SCHOOL_YEAR,
                    ], []);
                }
            }
        }
    }

    /**
     * Resolve (find or create) a teacher by name, applying alias canonicalisation
     * and generating a placeholder email. New teachers are attached to the given
     * fallback department.
     */
    private function resolveTeacher(string $name, int $fallbackDepartmentId, ?string $position = null): Teacher
    {
        $canonical = $this->canonicalTeacherName($name);

        if (isset($this->teacherCache[$canonical])) {
            $teacher = $this->teacherCache[$canonical];

            if ($position && ! $teacher->position) {
                $teacher->update(['position' => $position]);
            }

            return $teacher;
        }

        $email = Str::of($canonical)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.')
            ->value().'@ktu.edu.mm';

        $teacher = Teacher::firstOrCreate(
            ['email' => $email],
            [
                'department_id' => $fallbackDepartmentId,
                'name' => $canonical,
                'position' => $position,
            ],
        );

        if ($position && ! $teacher->position) {
            $teacher->update(['position' => $position]);
        }

        return $this->teacherCache[$canonical] = $teacher;
    }

    private function canonicalTeacherName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));

        return self::TEACHER_ALIASES[$name] ?? $name;
    }

    /** @return array<int, array{name: string, position?: string}> */
    private function civilTeachers(): array
    {
        return array_map(fn ($name) => ['name' => $name], [
            'Dr. Tint Ingyin Mar',
            'Daw Moet Moet Han',
            'Daw Mar Mar Htay',
            'Daw Nu Nu Kyi',
            'Daw Nandar Lwin',
            'Daw Khin Myo Nwet',
            'Daw Su Myat Lwin',
            'Daw Su Zin Zin Aung',
            'Daw Yadanar Mon',
            'Daw Khaing Khaing Lin',
            'Daw Hnin Ei Ei Khaing',
            'Daw Ei Phyu',
            'U Myint Than Naing',
            'Daw Hnin Yu Yu Lwin',
            'Daw Htar Sint Win',
            'Daw Hnin Hnin Shwe',
        ]);
    }

    /** @return array<int, array{name: string, position?: string}> */
    private function ceitTeachers(): array
    {
        return [
            ['name' => 'Dr. Khaing Myat Mon', 'position' => 'Head of Department'],
            ['name' => 'Dr. Nandar Lin'],
            ['name' => 'Dr. Nan Mo Kham'],
            ['name' => 'Daw Aye Kyaing'],
            ['name' => 'Daw Theingi Aung'],
            ['name' => 'U Thet Naing Htwe'],
            ['name' => 'Daw Aye Chan Moe'],
            ['name' => 'Daw Nway Nyein San'],
            ['name' => 'Daw Khin Thida Myint'],
            ['name' => 'Daw Yin Yin Win'],
            ['name' => 'Daw Sandar Khaing'],
        ];
    }

    /** @return array<int, array{year: int, name: string, subjects: array}> */
    private function civilCurriculum(): array
    {
        return [
            ['year' => 1, 'name' => 'First Year', 'subjects' => [
                ['code' => 'E-2011', 'name' => 'English', 'teachers' => ['Daw Khaing Mi Mi Htun']],
                ['code' => 'M-2001', 'name' => 'Myanmar', 'teachers' => ['Daw Myint Than Nwet']],
                ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['Dr. Ni Ni Aung']],
                ['code' => 'E.Ph-2001', 'name' => 'Engineering Physics II', 'teachers' => ['Daw Zar Zar Win', 'Daw Wint War Oo']],
                ['code' => 'ME-2002', 'name' => 'Industrial Safety and Workshop Practices', 'teachers' => ['Daw Khaing Yi Win']],
                ['code' => 'CE-2020', 'name' => 'Civil Engineering Drawing', 'teachers' => ['Daw Ei Phyu', 'U Myint Than Naing', 'Daw Khaing Khaing Lin']],
                ['code' => 'EM-1001', 'name' => 'Engineering Mathematics I (Retake)', 'teachers' => ['Daw Aye Thuzar Soe']],
            ]],
            ['year' => 2, 'name' => 'Second Year', 'subjects' => [
                ['code' => 'E-4032', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                ['code' => 'EM-4012', 'name' => 'Engineering Mathematics IV', 'teachers' => ['Daw Ank Phyu Win']],
                ['code' => 'EM-3002', 'name' => 'Engineering Mathematics III (Retake)', 'teachers' => ['Daw Myint Myint Nwe']],
                ['code' => 'CEIT-4001', 'name' => 'Computer Programming (CEIT)', 'teachers' => ['Dr. Khaing Myat Mon', 'Dr. Nan Mo Kham']],
                ['code' => 'CE-4001', 'name' => 'Surveying', 'teachers' => ['Daw Ei Phyu', 'U Myint Than Naing', 'Daw Khaing Khaing Lin']],
                ['code' => 'CE-4003', 'name' => 'Mechanics of Materials', 'teachers' => ['Daw Nandar Lwin']],
                ['code' => 'EG-4011', 'name' => 'Engineering Geology for Civil Engineer II', 'teachers' => ['Daw Hnin Hnin Shwe']],
            ]],
            ['year' => 3, 'name' => 'Third Year', 'subjects' => [
                ['code' => 'E-32011', 'name' => 'English', 'teachers' => ['Daw Thaw Thaw Maw']],
                ['code' => 'EM-32006', 'name' => 'Engineering Mathematics VI', 'teachers' => ['Daw Myint Myint Nwe']],
                ['code' => 'CE-32013', 'name' => 'Mechanics of Materials II', 'teachers' => ['Daw May Thin War']],
                ['code' => 'CE-32015', 'name' => 'Geotechnical Engineering II', 'teachers' => ['Daw Mar Mar Htay']],
                ['code' => 'CE-32016', 'name' => 'Fluids Mechanics II', 'teachers' => ['Daw Htar Sint Win']],
                ['code' => 'CE-32017', 'name' => 'Transportation Engineering II', 'teachers' => ['Daw Hnin Yu Yu Lwin']],
                ['code' => 'Geo-32011', 'name' => 'Civil Engineering Geology II', 'teachers' => ['Daw Hnin Hnin Shwe']],
                ['code' => 'CE-SP-32', 'name' => 'Surveying Project', 'teachers' => ['Daw Ei Phyu', 'U Myint Than Naing']],
            ]],
            ['year' => 4, 'name' => 'Fourth Year', 'subjects' => [
                ['code' => 'E-42011', 'name' => 'English', 'teachers' => ['Daw San Thidar']],
                ['code' => 'EM-42008', 'name' => 'Engineering Mathematics VIII', 'teachers' => ['Daw Khaing Khaing Htun']],
                ['code' => 'CE-42013', 'name' => 'Theory of Structures II', 'teachers' => ['Daw Hnin Ei Ei Khaing']],
                ['code' => 'CE-42016', 'name' => 'Hydraulic Engineering and Applied Hydraulic II', 'teachers' => ['Daw Khaing Khaing Lin']],
                ['code' => 'CE-42017', 'name' => 'Transportation Engineering IV', 'teachers' => ['Daw Yadanar Mon']],
                ['code' => 'CE-42024', 'name' => 'Design of Reinforced Concrete Structures II', 'teachers' => ['Daw Khin Myo Nwet']],
                ['code' => 'CE-42026', 'name' => 'Engineering Hydrology', 'teachers' => ['Daw Nu Nu Kyi']],
                ['code' => 'CE-TP-42', 'name' => 'Timber Project', 'teachers' => ['Daw Nu Nu Kyi']],
            ]],
            ['year' => 5, 'name' => 'Fifth Year', 'subjects' => [
                ['code' => 'CE-52012', 'name' => 'Construction Engineering Management II', 'teachers' => ['Daw Su Zin Zin Aung']],
                ['code' => 'CE-52015', 'name' => 'Foundation Engineering', 'teachers' => ['Daw Nandar Lwin']],
                ['code' => 'CE-52016', 'name' => 'Design of Hydraulic Structures II', 'teachers' => ['Dr. Tint Ingyin Mar']],
                ['code' => 'CE-52018', 'name' => 'Environmental Engineering II', 'teachers' => ['Daw Moet Moet Han']],
                ['code' => 'CE-52022', 'name' => 'Estimating and Specifications II', 'teachers' => ['Daw Su Myat Lwin']],
                ['code' => 'CE-52024', 'name' => 'Design of Steel Structures II', 'teachers' => ['Daw Khin Myo Nwet']],
                ['code' => 'IDP', 'name' => 'Integrated Design Project (IDP)', 'teachers' => ['Daw Khin Myo Nwet', 'Daw Mar Mar Htay', 'Daw Nu Nu Kyi', 'Daw Nandar Lwin']],
            ]],
            ['year' => 6, 'name' => 'Master (M.E)', 'subjects' => [
                ['code' => 'E-72011', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                ['code' => 'EM-72010', 'name' => 'Advanced Engineering Mathematics', 'teachers' => ['Dr. Ni Ni Aung']],
                ['code' => 'CE-72011', 'name' => 'Probability and Statistics', 'teachers' => ['Daw Nu Nu Kyi']],
                ['code' => 'CE-72015', 'name' => 'Advanced Soil Mechanics', 'teachers' => ['Daw Mar Mar Htay']],
                ['code' => 'CE-72016', 'name' => 'Surface, Surface Hydrology and Hydropower Engineering', 'teachers' => ['Dr. Tint Ingyin Mar']],
                ['code' => 'CE-72018', 'name' => 'Environmental Engineering', 'teachers' => ['Daw Moet Moet Han']],
            ]],
        ];
    }

    /** @return array<int, array{year: int, name: string, subjects: array}> */
    private function ceitCurriculum(): array
    {
        return [
            ['year' => 1, 'name' => 'First Year', 'subjects' => [
                ['code' => 'CEIT-2001', 'name' => 'C Programming', 'teachers' => ['Dr. Khaing Myat Mon', 'Dr. Nan Mo Kham']],
                ['code' => 'E.Ph-2001', 'name' => 'Engineering Physics II', 'teachers' => ['Daw Zar Zar Win']],
                ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['Daw Wint War Oo']],
                ['code' => 'EM-1001', 'name' => 'Engineering Mathematics I (Retake)', 'teachers' => ['Daw Zune Lei Nge']],
                ['code' => 'E-2011', 'name' => 'English', 'teachers' => ['Daw Aye Thuzar Soe']],
                ['code' => 'ME-2002', 'name' => 'Industrial Safety and Workshop Practices', 'teachers' => ['Daw Khaing Mi Mi Htun']],
                ['code' => 'M-2001', 'name' => 'Myanmar', 'teachers' => ['Daw Khin Khin Aye']],
            ]],
            ['year' => 2, 'name' => 'Second Year', 'subjects' => [
                ['code' => 'E-4032', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                ['code' => 'CEIT-4051', 'name' => 'Data Structure & Algorithm', 'teachers' => ['Daw Yin Yin Win']],
                ['code' => 'CEIT-4061', 'name' => 'Operating System', 'teachers' => ['Daw Nway Nyein San']],
                ['code' => 'CEIT-4002', 'name' => 'Digital Communication', 'teachers' => ['Daw Khin Thida Myint']],
                ['code' => 'CEIT-4021', 'name' => 'Java Programming', 'teachers' => ['U Thet Naing Htwe']],
                ['code' => 'CEIT-4041', 'name' => 'Database Management System', 'teachers' => ['Daw Sandar Khaing']],
                ['code' => 'EM-4012', 'name' => 'Engineering Mathematics IV', 'teachers' => []],
            ]],
            ['year' => 3, 'name' => 'Third Year', 'subjects' => [
                ['code' => 'CEIT-32035', 'name' => 'Web Development Technologies II', 'teachers' => ['Dr. Nandar Lin']],
                ['code' => 'CEIT-32045', 'name' => 'Programming Language in Java', 'teachers' => ['U Thet Naing Htwe']],
                ['code' => 'CEIT-32016', 'name' => 'Database Management System', 'teachers' => ['Daw Sandar Khaing']],
                ['code' => 'EM-32006', 'name' => 'Engineering Mathematics VI', 'teachers' => ['Daw Khaing Khaing Htun']],
                ['code' => 'CEIT-32022', 'name' => 'Computer Network', 'teachers' => ['Daw Aye Chan Moe']],
                ['code' => 'CEIT-32055', 'name' => 'Data Structure', 'teachers' => ['Daw Yin Yin Win']],
                ['code' => 'E-32011', 'name' => 'English', 'teachers' => ['U Khin Maung Myint']],
            ]],
            ['year' => 4, 'name' => 'Fourth Year', 'subjects' => [
                ['code' => 'CEIT-42026', 'name' => 'Advanced Data Management Techniques', 'teachers' => ['Dr. Nandar Lin']],
                ['code' => 'CEIT-42032', 'name' => 'Advanced Computer Network', 'teachers' => ['Daw Aye Kyaing']],
                ['code' => 'CEIT-42017', 'name' => 'Modern Control Systems', 'teachers' => ['Dr. Nandar Lin']],
                ['code' => 'CEIT-42023', 'name' => 'Computer Architecture & Organization', 'teachers' => ['Daw Khin Thida Myint']],
                ['code' => 'E-42011', 'name' => 'English', 'teachers' => ['Daw San Thidar']],
                ['code' => 'CEIT-42033', 'name' => 'Operating System', 'teachers' => ['Daw Nway Nyein San']],
                ['code' => 'EM-42008', 'name' => 'Engineering Mathematics VIII', 'teachers' => ['Daw Ank Phyu Win']],
            ]],
            ['year' => 5, 'name' => 'Fifth Year', 'subjects' => [
                // First semester
                ['code' => 'CEIT-51043', 'name' => 'Embedded System', 'semester' => 1, 'teachers' => ['Daw Aye Chan Moe']],
                ['code' => 'CEIT-51023', 'name' => 'Software Engineering', 'semester' => 1, 'teachers' => ['U Thet Naing Htwe']],
                ['code' => 'CEIT-51037', 'name' => 'Digital Image Processing', 'semester' => 1, 'teachers' => ['Daw Theingi Aung']],
                ['code' => 'CEIT-51014', 'name' => 'Cloud Computing', 'semester' => 1, 'teachers' => ['Daw Nway Nyein San']],
                ['code' => 'CEIT-51027', 'name' => 'Digital Signal Processing', 'semester' => 1, 'teachers' => []],
                // Second semester
                ['code' => 'CEIT-52043', 'name' => 'Embedded System', 'semester' => 2, 'teachers' => ['Daw Aye Chan Moe']],
                ['code' => 'CEIT-52065', 'name' => 'Software Engineering', 'semester' => 2, 'teachers' => ['U Thet Naing Htwe']],
                ['code' => 'CEIT-52037', 'name' => 'Digital Image Processing', 'semester' => 2, 'teachers' => ['Daw Theingi Aung']],
                ['code' => 'CEIT-52042', 'name' => 'Cryptography & Network Security', 'semester' => 2, 'teachers' => ['Daw Aye Kyaing']],
                ['code' => 'CEIT-52047', 'name' => 'Artificial Intelligence', 'semester' => 2, 'teachers' => ['Dr. Nan Mo Kham']],
            ]],
            ['year' => 6, 'name' => 'Master (M.E)', 'subjects' => [
                ['code' => 'CEIT-72062', 'name' => 'Information & Network Security', 'teachers' => ['Dr. Khaing Myat Mon']],
                ['code' => 'CEIT-72067', 'name' => 'Management Information System', 'teachers' => ['Daw Aye Kyaing']],
                ['code' => 'CEIT-72057', 'name' => 'Artificial Intelligence', 'teachers' => ['Dr. Nan Mo Kham']],
                ['code' => 'E-72011', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                ['code' => 'EM-72010', 'name' => 'Advanced Engineering Mathematics', 'teachers' => ['Dr. Ni Ni Aung']],
            ]],
        ];
    }
}

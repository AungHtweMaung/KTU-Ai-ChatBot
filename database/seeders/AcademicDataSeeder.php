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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds academic data (departments, majors, teachers, subjects, curriculum and
 * teacher assignments) transcribed from the university Google Sheet.
 *
 * The sheet is a single free-form worksheet in which each of the 9 engineering
 * departments occupies a row range: columns A-D hold that department's staff
 * roster, columns F-H hold its per-year curriculum and teaching assignments.
 * All 9 departments are transcribed here.
 *
 * Besides the 9 engineering departments the sheet's "Minor" list names 5
 * service departments (Engineering Mathematics, Engineering Physics,
 * Engineering Chemistry, English, Myanmar). Their teachers appear only in the
 * assignment column, never in a roster, so TEACHER_DEPARTMENTS maps each of
 * them to the right service department instead of letting them fall through to
 * whichever engineering department happens to reference them first.
 *
 * The sheet has no teacher emails, so placeholder emails are generated
 * (@ktu.edu.mm). It has no credit hours, so subjects keep the column default.
 * It states no school year; see SCHOOL_YEAR.
 *
 * Idempotent: safe to run repeatedly.
 */
class AcademicDataSeeder extends Seeder
{
    private const SCHOOL_YEAR = '2026-2027';

    /** The 9 engineering departments, each with one same-named major. */
    private const DEPARTMENTS = [
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

    /**
     * Service departments from the sheet's "Minor" list. These teach into every
     * engineering major and enrol no students of their own, so they get no major.
     */
    private const MINOR_DEPARTMENTS = [
        ['name' => 'Engineering Mathematics', 'code' => 'EM'],
        ['name' => 'Engineering Physics', 'code' => 'EPh'],
        ['name' => 'Engineering Chemistry', 'code' => 'EChem'],
        ['name' => 'English', 'code' => 'ENG'],
        ['name' => 'Myanmar', 'code' => 'MYAN'],
    ];

    /**
     * Teachers whose department cannot be inferred from a roster, keyed by
     * canonical name. Everyone here appears only in the assignment column.
     */
    private const TEACHER_DEPARTMENTS = [
        // Engineering Mathematics
        'Daw Myint Myint Nwe' => 'EM',
        'Daw Khaing Khaing Htun' => 'EM',
        'Daw Ank Phyu Win' => 'EM',
        'Daw Aye Thuzar Soe' => 'EM',
        'Dr. Ni Ni Aung' => 'EM',
        'Daw Zune Lei Nge' => 'EM',
        // Engineering Physics
        'Daw Zar Zar Win' => 'EPh',
        'Daw Wint War Oo' => 'EPh',
        // English
        'U Aung Kyaw Myint' => 'ENG',
        'Daw San Thidar' => 'ENG',
        'Daw Khaing Mi Mi Htun' => 'ENG',
        'Daw Thwe Thwe Maw' => 'ENG',
        'Daw Mon Mon Zin' => 'ENG',
        'Daw Nandar Win' => 'ENG',
        'U Khin Maung Nyunt' => 'ENG',
        // Myanmar
        'Daw Khin Khin Aye' => 'MYAN',
        'Daw Myint Than Nwet' => 'MYAN',
        // Engineering teachers absent from their own department's roster,
        // placed by the department that owns the subject they teach.
        'Daw Wah Wah Lwin' => 'EP',
        'Daw May Thin War' => 'Civil',
    ];

    /**
     * Canonicalise the spelling variants found in the source sheet.
     *
     * The Burmese entries come from the Mechanical Engineering block, which is
     * written entirely in Burmese script. Each one is the same person as an
     * English-named teacher elsewhere in the sheet; the first three were
     * confirmed by cross-matching subject codes across departments, the rest
     * are transliterations and may need the university's official spelling.
     */
    private const TEACHER_ALIASES = [
        // Latin-script spelling variants.
        'Dr.Tint Ingyin Mar' => 'Dr. Tint Ingyin Mar',
        'Dr. Khain Myat Mon' => 'Dr. Khaing Myat Mon',
        'Daw Sandar Khiang' => 'Daw Sandar Khaing',
        'Daw Aye Thu Zar Soe' => 'Daw Aye Thuzar Soe',
        'Daw Thaw Thaw Maw' => 'Daw Thaw Thaw Maw',
        'Daw Myint Myint Nwee' => 'Daw Myint Myint Nwe',
        'Daw Khaing Mi Mi Tun' => 'Daw Khaing Mi Mi Htun',
        'U Khaing Kyaw Tun' => 'U Khaing Kyaw Htun',
        'U Khin Maung Myint' => 'U Khin Maung Nyunt',
        'Daw Wint Wah Oo' => 'Daw Wint War Oo',
        'Daw Kyu Zin Phyoe' => 'Daw Kyu Zin Phyo',
        'Daw Wint War War Khine' => 'Daw Wint War Khine',
        'Daw Mya San Kyi' => 'Daw Mya San Yin',
        'Dr Myat Mon Aye' => 'Dr. Myat Mon Aye',

        // Mechanical Engineering roster, Burmese script.
        'ဒေါ်ခိုင်ရည်ဝင်း' => 'Daw Khaing Yi Win',
        'ဒေါ်စုနန္ဒာအောင်' => 'Daw Su Nandar Aung',
        'ဒေါ်အိရတနာဖြိုး' => 'Daw Ei Yadanar Phyo',
        'ဦးမျိုးကိုကိုအောင်' => 'U Myo Ko Ko Aung',
        'ဦးအောင်ညီညီဝင်း' => 'U Aung Nyi Nyi Win',
        'ဒေါက်တာ ခေမာသိမ့်' => 'Dr. Khema Thint',
        'ဒေါက်တာခေမာသိမ့်' => 'Dr. Khema Thint',
        'ဒေါ်ယုယုထွေး' => 'Daw Yu Yu Htwe',
        'ဒေါက်တာ ထက်ခိုင်' => 'Dr. Htet Khaing',

        // Teachers referenced in Burmese by the Mechanical Engineering block.
        'ဒေါ်ခင်ခင်အေး' => 'Daw Khin Khin Aye',
        'ဒေါ်မွန်မွန်ဇင်' => 'Daw Mon Mon Zin',
        'ဒေါ်အေးသူဇာစိုး' => 'Daw Aye Thuzar Soe',
        'ဒေါ်ဇာဇာဝင်း' => 'Daw Zar Zar Win',
        'ဒေါ်ဝင့်ဝါဦး' => 'Daw Wint War Oo',
        'ဦးအောင်ကျော်မြင့်' => 'U Aung Kyaw Myint',
        'ဒေါ်မြင့်မြင့်နွယ်' => 'Daw Myint Myint Nwe',
        'ဦးခိုင်ကျော်ထွန်း' => 'U Khaing Kyaw Htun',
        'ဒေါ်ဌေးဌေးစိုး' => 'Daw Htay Htay Soe',
        'ဒေါ်အံ့ဖြူဝင်း' => 'Daw Ank Phyu Win',
        'ဦးခင်မောင်ညွန့်' => 'U Khin Maung Nyunt',
        'ဒေါ်စန်းသီတာ' => 'Daw San Thidar',
    ];

    /**
     * Visiting staff who appear in the Mechatronic fifth-year block marked
     * "(online)" and in no roster.
     */
    private const ONLINE_TEACHERS = [
        'Daw Zaw Zaw Zin Phyu',
        'Daw Ei Wai Phyo',
        'Dr. Hnin Wai Wai Hlaing',
        'Dr. Saint Saint Pyone',
        'Daw Nu Nu Wai',
        'Daw Swe Zin Kyaw',
        'Daw Aye Thaint Thaint Kyaw',
    ];

    /**
     * Subject codes the sheet spells inconsistently between departments. Each
     * legacy spelling is folded into the canonical code so a shared service
     * subject is one row rather than one row per department.
     */
    private const SUBJECT_CODE_ALIASES = [
        'E.Ph-2001' => 'EPh-2001',
        'E.Ph- 2001' => 'EPh-2001',
        'Eph-2001' => 'EPh-2001',
        'Eng-2011' => 'E-2011',
        'Eng-4032' => 'E-4032',
        'Eng-42011' => 'E-42011',
        'Myan-2001' => 'M-2001',
        'Myan:M-2001' => 'M-2001',
        'CE_42016' => 'CE-42016',
    ];

    /** Cache of resolved Teacher models keyed by canonical name. */
    private array $teacherCache = [];

    /** @var array<string, Department> keyed by department code */
    private array $departments = [];

    public function run(): void
    {
        $this->normaliseSubjectCodes();

        $this->departments = $this->seedDepartments();

        $detail = [
            'Civil' => [$this->civilTeachers(), $this->civilCurriculum()],
            'CEIT' => [$this->ceitTeachers(), $this->ceitCurriculum()],
            'EC' => [$this->ecTeachers(), $this->ecCurriculum()],
            'EP' => [$this->epTeachers(), $this->epCurriculum()],
            'ME' => [$this->meTeachers(), $this->meCurriculum()],
            'MC' => [$this->mcTeachers(), $this->mcCurriculum()],
            'Met' => [$this->metTeachers(), $this->metCurriculum()],
            'NT' => [$this->ntTeachers(), $this->ntCurriculum()],
            'BioT' => [$this->bioTTeachers(), $this->bioTCurriculum()],
        ];

        // Every roster first, so a teacher listed by their own department is
        // placed there even when another department's curriculum names them
        // earlier. Only teachers on no roster at all fall back to the
        // department whose curriculum references them.
        foreach ($detail as $code => [$teachers, $_]) {
            $this->seedRoster($this->departments[$code], $teachers);
        }

        foreach ($detail as $code => [$_, $curriculum]) {
            $this->seedCurriculum($this->departments[$code], $curriculum);
        }

        foreach (self::ONLINE_TEACHERS as $name) {
            $this->resolveTeacher($name, $this->departments['MC']->id, 'Visiting Lecturer (online)');
        }
    }

    /**
     * Fold any subject row still carrying a legacy code into its canonical code,
     * moving curriculum rows (and their assignments) across before deleting the
     * duplicate. No-op once the data is normalised.
     */
    private function normaliseSubjectCodes(): void
    {
        foreach (self::SUBJECT_CODE_ALIASES as $legacy => $canonical) {
            if ($legacy === $canonical) {
                continue;
            }

            $old = Subject::where('code', $legacy)->first();

            // The lookup is case-insensitive under the default collation, so a
            // code differing from its alias only by case (Eph-2001 vs EPh-2001)
            // finds the already-canonical row. Compare exactly before touching it.
            if (! $old || $old->code === $canonical) {
                continue;
            }

            $new = Subject::where('code', $canonical)->first();

            // Nothing to merge into: a rename is enough.
            if (! $new || $new->id === $old->id) {
                $old->update(['code' => $canonical]);

                continue;
            }

            foreach (CurriculumSubject::where('subject_id', $old->id)->get() as $row) {
                $existing = CurriculumSubject::where([
                    'major_id' => $row->major_id,
                    'major_year_id' => $row->major_year_id,
                    'subject_id' => $new->id,
                    'semester' => $row->semester,
                ])->first();

                if (! $existing) {
                    $row->update(['subject_id' => $new->id]);

                    continue;
                }

                // The canonical curriculum row already exists; move the
                // assignments onto it and drop the duplicate.
                foreach (TeacherAssignment::where('curriculum_subject_id', $row->id)->get() as $assignment) {
                    $clash = TeacherAssignment::where([
                        'teacher_id' => $assignment->teacher_id,
                        'curriculum_subject_id' => $existing->id,
                        'school_year' => $assignment->school_year,
                    ])->exists();

                    $clash
                        ? $assignment->delete()
                        : $assignment->update(['curriculum_subject_id' => $existing->id]);
                }

                $row->delete();
            }

            $old->delete();
        }
    }

    /**
     * Create the 9 engineering departments (each with one same-named major) and
     * the 5 service departments from the sheet's "Minor" list (no major).
     *
     * @return array<string, Department> keyed by department code
     */
    private function seedDepartments(): array
    {
        $departments = [];

        foreach (self::DEPARTMENTS as $row) {
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

        foreach (self::MINOR_DEPARTMENTS as $row) {
            $departments[$row['code']] = Department::updateOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name']],
            );
        }

        return $departments;
    }

    /**
     * Seed one department's staff roster.
     *
     * @param  array<int, array{name: string, position?: string, phone?: string}>  $teachers
     */
    private function seedRoster(Department $department, array $teachers): void
    {
        foreach ($teachers as $teacher) {
            $this->resolveTeacher(
                $teacher['name'],
                $department->id,
                $teacher['position'] ?? null,
                $teacher['phone'] ?? null,
                roster: true,
            );
        }
    }

    /**
     * Seed one department's years, subjects, curriculum and teacher assignments.
     *
     * @param  array<int, array{year: int, name: string, subjects: array}>  $curriculum
     */
    private function seedCurriculum(Department $department, array $curriculum): void
    {
        $major = $department->majors()->firstOrFail();

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
     * Resolve (find or create) a teacher by name, applying alias
     * canonicalisation and generating a placeholder email.
     *
     * A teacher named in TEACHER_DEPARTMENTS is always placed in that
     * department, and a teacher on a department's own roster ($roster) is
     * always placed in that department. Both correct the record if it already
     * exists elsewhere. Everyone else falls back to the department being
     * seeded, which for a teacher on no roster is the department whose
     * curriculum references them.
     */
    private function resolveTeacher(
        string $name,
        int $fallbackDepartmentId,
        ?string $position = null,
        ?string $phone = null,
        bool $roster = false,
    ): Teacher {
        $canonical = $this->canonicalTeacherName($name);

        $mapped = self::TEACHER_DEPARTMENTS[$canonical] ?? null;
        $departmentId = $mapped
            ? $this->departments[$mapped]->id
            : $fallbackDepartmentId;

        $authoritative = ($mapped || $roster) ? $departmentId : null;

        if (isset($this->teacherCache[$canonical])) {
            return $this->fillTeacher($this->teacherCache[$canonical], $canonical, $authoritative, $position, $phone);
        }

        $email = Str::of($canonical)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.')
            ->value().'@ktu.edu.mm';

        $teacher = Teacher::firstOrCreate(
            ['email' => $email],
            [
                'department_id' => $departmentId,
                'name' => $canonical,
                'position' => $position,
                'phone' => $phone,
            ],
        );

        $this->teacherCache[$canonical] = $teacher;

        return $this->fillTeacher($teacher, $canonical, $authoritative, $position, $phone);
    }

    /**
     * Apply details that may arrive after the teacher row was first created.
     *
     * The canonical name and an explicitly mapped department always win, so a
     * row matched by its generated email (which ignores punctuation, making
     * "Dr Khaing Myat Mon" and "Dr. Khaing Myat Mon" the same record) is pulled
     * back to the canonical spelling. Position and phone only fill a gap.
     */
    private function fillTeacher(
        Teacher $teacher,
        string $canonical,
        ?int $departmentId,
        ?string $position,
        ?string $phone,
    ): Teacher {
        $changes = [];

        if ($teacher->name !== $canonical) {
            $changes['name'] = $canonical;
        }

        if ($departmentId && $teacher->department_id !== $departmentId) {
            $changes['department_id'] = $departmentId;
        }

        if ($position && ! $teacher->position) {
            $changes['position'] = $position;
        }

        if ($phone && ! $teacher->phone) {
            $changes['phone'] = $phone;
        }

        if ($changes) {
            $teacher->update($changes);
        }

        return $teacher;
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

    /** @return array<int, array{name: string, position?: string, phone?: string}> */
    private function ceitTeachers(): array
    {
        return [
            ['name' => 'Dr. Khaing Myat Mon', 'position' => 'Head of Department / Professor', 'phone' => '09 442 078 050'],
            ['name' => 'Dr. Nandar Lin', 'position' => 'Associate Professor', 'phone' => '09 775 247 986'],
            ['name' => 'Dr. Nan Mo Kham', 'position' => 'Deputy Head of Department / Associate Professor', 'phone' => '09 400 421 284'],
            ['name' => 'Daw Aye Kyaing', 'position' => 'Associate Professor', 'phone' => '09 689 993 390'],
            ['name' => 'Daw Theingi Aung', 'position' => 'Demonstrator', 'phone' => '09 970 542 175'],
            ['name' => 'U Thet Naing Htwe', 'position' => 'Lecturer', 'phone' => '09 797 595 917'],
            ['name' => 'Daw Aye Chan Moe', 'position' => 'Demonstrator', 'phone' => '09 755 052 508'],
            ['name' => 'Daw Nway Nyein San', 'position' => 'Demonstrator', 'phone' => '09 756 714 845'],
            ['name' => 'Daw Khin Thida Myint', 'position' => 'Demonstrator', 'phone' => '09 983 414 809'],
            ['name' => 'Daw Yin Yin Win', 'position' => 'Demonstrator', 'phone' => '09 402 556 151'],
            ['name' => 'Daw Sandar Khaing', 'position' => 'Demonstrator', 'phone' => '09 402 562 152'],
            ['name' => 'Daw Phyo Zarni Oo', 'position' => 'Office Assistant', 'phone' => '09 772 384 378'],
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
                ['code' => 'EPh-2001', 'name' => 'Engineering Physics', 'teachers' => ['Daw Zar Zar Win', 'Daw Wint War Oo']],
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
                ['code' => 'EPh-2001', 'name' => 'Engineering Physics', 'teachers' => ['Daw Zar Zar Win']],
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

    /** @return array<int, array{name: string, position?: string, phone?: string}> */
    private function ecTeachers(): array
    {
        return [
            ['name' => 'Dr. Aye Theingi Oo'],
            ['name' => 'Dr. Aye Mya Win', 'position' => 'Head of Department'],
            ['name' => 'Daw Yin Nwe Win'],
            ['name' => 'Daw Ni Ni San Hlaing'],
            ['name' => 'Daw Nway Nway Hlaing'],
            ['name' => 'Daw Poe Au Phyu'],
            ['name' => 'Daw Htay Htay Soe'],
            ['name' => 'Daw Kyu Zin Phyo'],
            ['name' => 'Daw Su Sandar'],
            ['name' => 'Daw Hsu Nwe Wai'],
            ['name' => 'Daw Yu Yu Swe'],
            ['name' => 'Daw Kay Kay Moe'],
            ['name' => 'Daw Wint War Khine'],
        ];
    }

    /** @return array<int, array{year: int, name: string, subjects: array}> */
    private function ecCurriculum(): array
    {
        return [
            [
                'year' => 1,
                'name' => 'First Year',
                // Sheet: First Year IEC, Family-Daw Su Sandar
                'subjects' => [
                    ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['Daw Zune Lei Nge']],
                    ['code' => 'E-2011', 'name' => 'English', 'teachers' => ['Daw Mon Mon Zin']],
                    ['code' => 'M-2001', 'name' => 'Myanmar', 'teachers' => ['Daw Myint Than Nwet']],
                    ['code' => 'EPh-2001', 'name' => 'Engineering Physics', 'teachers' => ['Daw Zar Zar Win', 'Daw Wint War Oo']],
                    ['code' => 'ME-2002', 'name' => 'Industrial Safety and Workshop Practices', 'teachers' => ['Daw Ei Yadanar Phyo']],
                    ['code' => 'EcE-2002', 'name' => 'Engineering Circuit Analysis', 'teachers' => ['Daw Su Sandar']],
                ],
            ],
            [
                'year' => 2,
                'name' => 'Second Year',
                // Sheet: Second Year IIEC, Family-Daw Kyu Zin Phyoe
                'subjects' => [
                    ['code' => 'EM-4012', 'name' => 'Engineering Mathematics IV', 'teachers' => ['Daw Ank Phyu Win']],
                    ['code' => 'E-4032', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                    ['code' => 'ME-4015', 'name' => 'Engineering Mechanics', 'teachers' => ['Daw Su Nandar Aung']],
                    ['code' => 'EcE-4014', 'name' => 'Techical Programming', 'teachers' => ['Daw Kyu Zin Phyoe']],
                    ['code' => 'EcE-4005', 'name' => 'Signals and Systems', 'teachers' => ['Dr. Aye Theingi Oo']],
                    ['code' => 'EcE-4002', 'name' => 'Fundamental Communication', 'teachers' => ['Daw Yu Yu Swe']],
                    ['code' => 'EcE-4111', 'name' => 'Engineering Practices Laboratory(Ciruit & Digital)', 'teachers' => ['Daw Nway Nway Hlaing', 'Daw Wint War War Khine']],
                ],
            ],
            [
                'year' => 3,
                'name' => 'Third Year',
                // Sheet: Third year IIIEC, Family-Dr. Aye Theingi Oo
                'subjects' => [
                    ['code' => 'EM-32006', 'name' => 'Engineering Mathematics VI', 'teachers' => ['Daw Khaing Khaing Htun']],
                    ['code' => 'E-32011', 'name' => 'English', 'teachers' => ['Daw Thwe Thwe Maw']],
                    ['code' => 'EcE-32001', 'name' => 'Engineering Circuit Analysis II', 'teachers' => ['Daw Htay Htay Soe']],
                    ['code' => 'EcE-32002', 'name' => 'Digital Communication II', 'teachers' => ['Dr. Aye Theingi Oo']],
                    ['code' => 'EcE-32003', 'name' => 'Modeling And Conrtol II', 'teachers' => ['Daw Kay Kay Moe']],
                    ['code' => 'EcE-32011', 'name' => 'Engineering Electromagnaetic II', 'teachers' => ['Daw Poe Au Phyu']],
                    ['code' => 'EcE-32021', 'name' => 'Integrated Electronics II', 'teachers' => ['Daw Wint War War Khine']],
                ],
            ],
            [
                'year' => 4,
                'name' => 'Fourth Year',
                // Sheet: Fourth Year IVEC, Family-Daw Yin Nwe Win
                'subjects' => [
                    ['code' => 'EM-42008', 'name' => 'Engineering Mathematics VIII', 'teachers' => ['Daw Ank Phyu Win']],
                    ['code' => 'E-42011', 'name' => 'English', 'teachers' => ['Daw San Thidar']],
                    ['code' => 'EP-42043', 'name' => 'Electrial Machine', 'teachers' => ['Daw Wah Wah Lwin']],
                    ['code' => 'EcE-42002', 'name' => 'Computer Networking', 'teachers' => ['Daw Kyu Zin Phyoe']],
                    ['code' => 'EcE-42003', 'name' => 'Modern Control System II', 'teachers' => ['Daw Nway Nway Hlaing']],
                    ['code' => 'EcE-42021', 'name' => 'Digital Design With HDL II', 'teachers' => ['Daw Kay Kay Moe']],
                    ['code' => 'EcE-42031', 'name' => 'Programmable Logic Controllers', 'teachers' => ['Daw Yin Nwe Win']],
                ],
            ],
            [
                'year' => 5,
                'name' => 'Fifth Year',
                // Sheet: Fifth Year(second semester) VEC, Family-Daw Ni Ni San Hlaing
                'subjects' => [
                    ['code' => 'EcE-52001', 'name' => 'Advanced Electronics II', 'semester' => 2, 'teachers' => ['Daw Yu Yu Swe']],
                    ['code' => 'EcE-52003', 'name' => 'Digital Control System II', 'semester' => 2, 'teachers' => ['Daw Ni Ni San Hlaing']],
                    ['code' => 'EcE-51005', 'name' => 'Digital Signal Processing II', 'semester' => 2, 'teachers' => ['Daw Hsu Nwe Wai']],
                    ['code' => 'EcE-52006', 'name' => 'Industrial Management II', 'semester' => 2, 'teachers' => ['Dr. Aye Mya Win']],
                    ['code' => 'EcE-52012', 'name' => 'Modern Electronic Communication System II', 'semester' => 2, 'teachers' => ['Daw Su Sandar']],
                    ['code' => 'EcE-52013', 'name' => 'Microwave Engineering II', 'semester' => 2, 'teachers' => ['Daw Poe Au Phyu']],
                    ['code' => 'EcE-52007', 'name' => 'Integrated Design Project', 'semester' => 2, 'teachers' => ['Daw Poe Au Phyu', 'Daw Htay Htay Soe', 'Daw Kyu Zin Phyo']],
                    ['code' => 'EcE-52033', 'name' => 'PLC Programming Methods and Techniques II', 'semester' => 2, 'teachers' => ['Daw Yin Nwe Win']],
                ],
            ],
            [
                'year' => 6,
                'name' => 'Master (M.E)',
                // Sheet: ME EC, Family- Daw Ni Ni San Hlaing, Daw Nway Nway Hlaing
                'subjects' => [
                    ['code' => 'E-72011', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                    ['code' => 'EM-72010', 'name' => 'Advanced Engineering Mathematics', 'teachers' => ['Dr. Ni Ni Aung']],
                    ['code' => 'EcE-72001', 'name' => 'Industrial Electronics', 'teachers' => ['Daw Nway Nway Hlaing']],
                    ['code' => 'EcE-72002', 'name' => 'Microwave Circuit Design', 'teachers' => ['Dr. Aye Theingi Oo']],
                    ['code' => 'EcE-72003', 'name' => 'Fuzzy Logic and Neural Network', 'teachers' => ['Daw Ni Ni San Hlaing']],
                    ['code' => 'EcE-72004', 'name' => 'Digital Image Processing', 'teachers' => ['Daw Yin Nwe Win']],
                ],
            ],
        ];
    }

    /** @return array<int, array{name: string, position?: string, phone?: string}> */
    private function epTeachers(): array
    {
        return [
            ['name' => 'Dr. Su Hlaing Myint', 'position' => 'Head of Department / Professor'],
            ['name' => 'Daw Thae Thae Mon'],
            ['name' => 'Daw Ni Lar Myo'],
            ['name' => 'Daw Myat Thandar Soe'],
            ['name' => 'Daw Myo Thandar Khaing'],
            ['name' => 'Daw Yin Mar Htun'],
            ['name' => 'Dr. Soe Soe Than'],
            ['name' => 'Daw Lin Lin Soe'],
            ['name' => 'Daw Thazin Kyaw Win'],
            ['name' => 'Daw Khin Myo Than'],
            ['name' => 'Daw Moe Nge Nge'],
        ];
    }

    /** @return array<int, array{year: int, name: string, subjects: array}> */
    private function epCurriculum(): array
    {
        return [
            [
                'year' => 1,
                'name' => 'First Year',
                // Sheet: First Year IEP, Family-Daw Thae Thae Mon
                'subjects' => [
                    ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['Daw Zune Lei Nge']],
                    ['code' => 'E-2011', 'name' => 'English', 'teachers' => ['Daw Nandar Win']],
                    ['code' => 'M-2001', 'name' => 'Myanmar', 'teachers' => ['Daw Myint Than Nwet']],
                    ['code' => 'EPh-2001', 'name' => 'Engineering Physics', 'teachers' => ['Daw Zar Zar Win', 'Daw Wint Wah Oo']],
                    ['code' => 'ME-2002', 'name' => 'Industrial Safety and Workshop Practices', 'teachers' => ['Daw Ei Yadanar Phyo']],
                    ['code' => 'EP-2014', 'name' => 'Digital Electronics', 'teachers' => ['Daw Thae Thae Mon']],
                ],
            ],
            [
                'year' => 2,
                'name' => 'Second Year',
                // Sheet: Second Year IIEP, Family - Daw Ni Lar Myo
                'subjects' => [
                    ['code' => 'EM-4012', 'name' => 'Engineering Mathematics IV', 'teachers' => ['Daw Myint Myint Nwe']],
                    ['code' => 'E-4032', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                    ['code' => 'ME-4015', 'name' => 'Engineering Mechanics', 'teachers' => ['Daw Su Nandar Aung']],
                    ['code' => 'EP-4034', 'name' => 'Electrical Design Estimating and Costing', 'teachers' => ['Daw Myat Thandar Soe']],
                    ['code' => 'EP-4021', 'name' => 'Electromechanics', 'teachers' => ['Daw Ni Lar Myo']],
                    ['code' => 'EP-4011', 'name' => 'Electrical Engineering Circuit Analysis II', 'teachers' => ['Daw Myo Thandar Khaing']],
                ],
            ],
            [
                'year' => 3,
                'name' => 'Third Year',
                // Sheet: Third Year IIIEP, Family - Daw Yin Mar Htun
                'subjects' => [
                    ['code' => 'EM-32006', 'name' => 'Engineering Mathematics VI', 'teachers' => ['Daw Khaing Khaing Htun']],
                    ['code' => 'E-32011', 'name' => 'English', 'teachers' => ['Daw Thwe Thwe Maw']],
                    ['code' => 'ME-32034', 'name' => 'Mechanical engineering fundamentals', 'teachers' => ['Daw Khaing Yi Win']],
                    ['code' => 'EP-32011', 'name' => 'electrical engineering circuit analysis IV', 'teachers' => ['Dr. Su Hlaing Myint']],
                    ['code' => 'EP-32014', 'name' => 'Power electronic II', 'teachers' => ['Daw Yin Mar Htun']],
                    ['code' => 'EP-32021', 'name' => 'electrical Machine and operation II', 'teachers' => ['Dr. Soe Soe Than']],
                    ['code' => 'EP-32033', 'name' => 'electromagnetic field II', 'teachers' => ['Daw Ni Lar Myo']],
                    ['code' => 'EP-32034', 'name' => 'electrical design, estimating and costing', 'teachers' => ['Daw Lin Lin Soe']],
                ],
            ],
            [
                'year' => 4,
                'name' => 'Fourth Year',
                // Sheet: Fourth Year IVEP, Family - Daw Thazin Kyaw Win
                'subjects' => [
                    ['code' => 'EM-42008', 'name' => 'Engineering Mathematics VIII', 'teachers' => ['Daw Ank Phyu Win']],
                    ['code' => 'E-42011', 'name' => 'English', 'teachers' => ['Daw San Thidar']],
                    ['code' => 'EcE-42024', 'name' => 'Computer Science', 'teachers' => []],
                    ['code' => 'EP-42027', 'name' => 'Linear Control System II', 'teachers' => ['Daw Myo Thandar Khaing']],
                    ['code' => 'EP-42028', 'name' => 'Programmable Logic Control II', 'teachers' => ['Dr. Soe Soe Than']],
                    ['code' => 'EP-42021', 'name' => 'Electrical Machine Design', 'teachers' => ['Daw Yin Mar Htun']],
                    ['code' => 'EP-42036', 'name' => 'Design & Layout of Power', 'teachers' => ['Daw Thazin Kyaw Win']],
                    ['code' => 'EP-42042', 'name' => 'Power System Analysis II', 'teachers' => ['Daw Khin Myo Than']],
                ],
            ],
            [
                'year' => 5,
                'name' => 'Fifth Year',
                // Sheet: Fifth Year VEP, Family - Daw Lin Lin Soe
                'subjects' => [
                    ['code' => 'EP-51022', 'name' => 'Power System Protection I', 'teachers' => ['Daw Myat Thandar Soe']],
                    ['code' => 'EP-51017', 'name' => 'Modern Control System I', 'teachers' => ['Daw Moe Nge Nge']],
                    ['code' => 'EP-51002', 'name' => 'Power System Stability I', 'teachers' => ['Daw Lin Lin Soe']],
                    ['code' => 'EP-51043', 'name' => 'Electromechanics Energy Conversion I', 'teachers' => ['Daw Thae Thae Mon']],
                    ['code' => 'EP-51014', 'name' => 'Electrical Machine and Control I', 'teachers' => ['Daw Khin Myo Than']],
                ],
            ],
            [
                'year' => 6,
                'name' => 'Master (M.E)',
                // Sheet: ME EP, Family - Daw Moe Nge Nge
                'subjects' => [
                    ['code' => 'EM-72010', 'name' => 'Advanced Engineering Mathematics', 'teachers' => ['Dr. Ni Ni Aung']],
                    ['code' => 'E-72011', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                    ['code' => 'EP-72006', 'name' => 'Electrical Power System Quality', 'teachers' => ['Dr. Su Hlaing Myint']],
                    ['code' => 'EP-72005', 'name' => 'Renewable and Efficient Electric Power System', 'teachers' => ['Daw Moe Nge Nge']],
                    ['code' => 'EP-72004', 'name' => 'Discrete-time Control System', 'teachers' => ['Dr. Soe Soe Than']],
                ],
            ],
        ];
    }

    /** @return array<int, array{name: string, position?: string, phone?: string}> */
    private function meTeachers(): array
    {
        return [
            ['name' => 'ဒေါ်ခိုင်ရည်ဝင်း'],
            ['name' => 'ဦးမျိုးကိုကိုအောင်'],
            ['name' => 'ဒေါ်စုနန္ဒာအောင်'],
            ['name' => 'ဒေါ်အိရတနာဖြိုး'],
            ['name' => 'ဦးအောင်ညီညီဝင်း'],
            ['name' => 'ဒေါက်တာ ခေမာသိမ့်'],
            ['name' => 'ဒေါ်ယုယုထွေး'],
            ['name' => 'ဒေါက်တာ ထက်ခိုင်'],
        ];
    }

    /** @return array<int, array{year: int, name: string, subjects: array}> */
    private function meCurriculum(): array
    {
        return [
            [
                'year' => 1,
                'name' => 'First Year',
                // Sheet: First Year IME
                'subjects' => [
                    ['code' => 'M-2001', 'name' => 'Myanmar', 'teachers' => ['ဒေါ်ခင်ခင်အေး']],
                    ['code' => 'E-2011', 'name' => 'English', 'teachers' => ['ဒေါ်မွန်မွန်ဇင်']],
                    ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['ဒေါ်အေးသူဇာစိုး']],
                    ['code' => 'ME-2002', 'name' => 'Industrial Safety and Workshop Practices', 'teachers' => ['ဒေါ်ခိုင်ရည်ဝင်း']],
                    ['code' => 'EPh-2001', 'name' => 'Engineering Physics', 'teachers' => ['ဒေါ်ဇာဇာဝင်း', 'ဒေါ်ဝင့်ဝါဦး']],
                    ['code' => 'ME-2011', 'name' => 'Computer Aided Machine Drawing', 'teachers' => ['ဦးမျိုးကိုကိုအောင်']],
                    ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['ဒေါ်အေးသူဇာစိုး']],
                ],
            ],
            [
                'year' => 2,
                'name' => 'Second Year',
                // Sheet: Second Year IIME
                'subjects' => [
                    ['code' => 'E-4032', 'name' => 'English', 'teachers' => ['ဦးအောင်ကျော်မြင့်']],
                    ['code' => 'EM-3002', 'name' => 'Engineering Mathematics III (Retake)', 'teachers' => ['ဒေါ်မြင့်မြင့်နွယ်']],
                    ['code' => 'Met-4014', 'name' => 'Engineering Materials', 'teachers' => ['ဦးခိုင်ကျော်ထွန်း']],
                    ['code' => 'EcE-4025', 'name' => 'Applied Electrical Engineering', 'teachers' => ['ဒေါ်ဌေးဌေးစိုး']],
                    ['code' => 'EM-4012', 'name' => 'Engineering Mathematics IV', 'teachers' => ['ဒေါ်အံ့ဖြူဝင်း']],
                    ['code' => 'ME-4015', 'name' => 'Engineering Mechanics', 'teachers' => ['ဒေါ်စုနန္ဒာအောင်']],
                    ['code' => 'ME-4013', 'name' => 'Engineering Thermodynamics', 'teachers' => ['ဒေါ်စုနန္ဒာအောင်']],
                    ['code' => 'ME-3005', 'name' => 'Engineering Mechanic, Retake', 'teachers' => ['ဒေါ်စုနန္ဒာအောင်']],
                ],
            ],
            [
                'year' => 3,
                'name' => 'Third Year',
                // Sheet: Third Year IIIME
                'subjects' => [
                    ['code' => 'E-32011', 'name' => 'English', 'teachers' => ['ဦးခင်မောင်ညွန့်']],
                    ['code' => 'EM-32006', 'name' => 'Engineering Mathematics VI', 'teachers' => ['ဒေါ်မြင့်မြင့်နွယ်']],
                    ['code' => 'ME-32013', 'name' => 'Engineering Thermodynamics', 'teachers' => ['ဒေါ်စုနန္ဒာအောင်']],
                    ['code' => 'EcE-32025', 'name' => 'Analogue & Digital Electronics', 'teachers' => ['ဒေါ်ဌေးဌေးစိုး']],
                    ['code' => 'ME-32014', 'name' => 'Strength of Materials II', 'teachers' => ['ဒေါ်အိရတနာဖြိုး']],
                    ['code' => 'ME-32015', 'name' => 'Theory of Machines I', 'teachers' => ['ဦးမျိုးကိုကိုအောင်']],
                    ['code' => 'ME-32022', 'name' => 'Production Technology', 'teachers' => ['ဦးအောင်ညီညီဝင်း']],
                ],
            ],
            [
                'year' => 4,
                'name' => 'Fourth Year',
                // Sheet: Fourth Year IVME
                'subjects' => [
                    ['code' => 'E-42011', 'name' => 'English', 'teachers' => ['ဒေါ်စန်းသီတာ']],
                    ['code' => 'EM-42008', 'name' => 'Engineering Mathematics VIII', 'teachers' => ['ဒေါ်အံ့ဖြူဝင်း']],
                    ['code' => 'ME-42031', 'name' => 'Designs of machine elements', 'teachers' => ['ဒေါ်အိရတနာဖြိုး']],
                    ['code' => 'ME-42032', 'name' => 'Manufacturing System And Automations', 'teachers' => ['ဦးအောင်ညီညီဝင်း']],
                    ['code' => 'ME-42019', 'name' => 'Computer Applications in Mechanical Engineering', 'teachers' => ['ဒေါက်တာခေမာသိမ့်']],
                    ['code' => 'ME-42033', 'name' => 'Heat Transfer', 'teachers' => ['ဒေါ်ယုယုထွေး']],
                    ['code' => 'ME-42015', 'name' => 'Theory of Machines II', 'teachers' => ['ဦးမျိုးကိုကိုအောင်']],
                    ['code' => 'ME-42016', 'name' => 'Fluid Mechanics I', 'teachers' => ['ဦးမျိုးကိုကိုအောင်']],
                ],
            ],
            [
                'year' => 5,
                'name' => 'Fifth Year',
                // Sheet: Fifth Year VME
                'subjects' => [
                    ['code' => 'ME-52015', 'name' => 'Vibration and Control', 'teachers' => ['ဒေါက်တာ ခေမာသိမ့်']],
                    ['code' => 'ME-52016', 'name' => 'Fluid Mechanics II', 'teachers' => ['ဒေါက်တာ ခေမာသိမ့်']],
                    ['code' => 'ME-52017', 'name' => 'Refrigeration & Air-conditioning', 'teachers' => ['ဒေါ်စုနန္ဒာအောင်']],
                    ['code' => 'ME-52023', 'name' => 'Internal Combustion Engines', 'teachers' => ['ဒေါက်တာ ထက်ခိုင်']],
                    ['code' => 'ME-52028', 'name' => 'Industrial Engineering and Management', 'teachers' => ['ဦးအောင်ညီညီဝင်း']],
                    ['code' => 'ME-52031', 'name' => 'Integrated Design Project', 'teachers' => ['ဒေါက်တာ ထက်ခိုင်']],
                    ['code' => 'ME-52043', 'name' => 'Gas Turbine Theory', 'teachers' => ['ဒေါ်ခိုင်ရည်ဝင်း']],
                ],
            ],
            [
                'year' => 6,
                'name' => 'Master (M.E)',
                // Sheet: Master ME
                'subjects' => [
                    ['code' => 'E-72011', 'name' => 'English', 'teachers' => ['ဦးအောင်ကျော်မြင့်']],
                    ['code' => 'EM-72010', 'name' => 'Advanced Engineering Mathematics', 'teachers' => ['ဒေါ်အံ့ဖြူဝင်း']],
                    ['code' => 'ME-72037', 'name' => 'Farm Machinery', 'teachers' => ['ဒေါ်ယုယုထွေး']],
                    ['code' => 'ME-72032', 'name' => 'Advanced Internal Combustion Engine', 'teachers' => ['ဒေါက်တာ ထက်ခိုင်']],
                    ['code' => 'ME-72033', 'name' => 'Heat and Mass Transfer', 'teachers' => ['ဦးအောင်ညီညီဝင်း']],
                ],
            ],
        ];
    }

    /** @return array<int, array{name: string, position?: string, phone?: string}> */
    private function mcTeachers(): array
    {
        return [
            ['name' => 'Daw May Thazin Aung'],
            ['name' => 'Daw Hsu Myat Hlaing'],
            ['name' => 'Daw Myo Myo Zin'],
            ['name' => 'Daw Nyein Chan Kyi'],
            ['name' => 'Daw Mya San Kyi'],
            ['name' => 'Daw Aye Aye Moe'],
            ['name' => 'Daw Mya San Yin'],
        ];
    }

    /** @return array<int, array{year: int, name: string, subjects: array}> */
    private function mcCurriculum(): array
    {
        return [
            [
                'year' => 1,
                'name' => 'First Year',
                // Sheet: First Year IMC, Family - Daw May Thazin Aung
                'subjects' => [
                    ['code' => 'M-2001', 'name' => 'Myanmar', 'teachers' => ['Daw Khin Khin Aye']],
                    ['code' => 'E-2011', 'name' => 'English', 'teachers' => ['Daw Nandar Win']],
                    ['code' => 'EPh-2001', 'name' => 'Engineering Physics', 'teachers' => ['Daw Zar Zar Win', 'Daw Wint War Oo']],
                    ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['Daw Khaing Khaing Htun']],
                    ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['Daw Aye Thuzar Soe']],
                    ['code' => 'McE-2012', 'name' => 'Factory Control Engineering II', 'teachers' => ['Daw May Thazin Aung']],
                ],
            ],
            [
                'year' => 2,
                'name' => 'Second Year',
                // Sheet: Second Year IIMC, Family - Daw Hsu Myat Hlaing
                'subjects' => [
                    ['code' => 'E-4032', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                    ['code' => 'EM-4012', 'name' => 'Engineering Mathematics IV', 'teachers' => ['Daw Myint Myint Nwe']],
                    ['code' => 'EM-3002', 'name' => 'Engineering Mathematics III (Retake)', 'teachers' => ['Daw Myint Myint Nwe']],
                    ['code' => 'McE-4026', 'name' => 'Engineering Devices and Circuits II', 'teachers' => ['Daw Aye Aye Moe']],
                    ['code' => 'McE-4019', 'name' => 'Computer Architecture and Programming II', 'teachers' => ['Daw Mya San Yin']],
                    ['code' => 'McE-4015', 'name' => 'Engineering Mechanics II', 'teachers' => ['Daw Nyein Chan Kyi']],
                    ['code' => 'McE-4046', 'name' => 'Digital Electronics II', 'teachers' => ['Daw Myo Myo Zin']],
                    ['code' => 'McE-4049', 'name' => 'Programmable Logic Controller', 'teachers' => ['Daw Hsu Myat Hlaing']],
                ],
            ],
            [
                'year' => 3,
                'name' => 'Third Year',
                // Sheet: Third Year IIIMC, Family - Daw Myo Myo Zin
                'subjects' => [
                    ['code' => 'E-32011', 'name' => 'English', 'teachers' => ['U Khin Maung Nyunt']],
                    ['code' => 'EM-32006', 'name' => 'Engineering Mathematics VI', 'teachers' => ['Daw Khaing Khaing Htun']],
                    ['code' => 'McE-32026', 'name' => 'Electronic Devices II', 'teachers' => ['Daw Aye Aye Moe']],
                    ['code' => 'McE-32036', 'name' => 'Digital Electronics II', 'teachers' => ['Daw Myo Myo Zin']],
                    ['code' => 'McE-32032', 'name' => 'Electrical Machine and Control', 'teachers' => ['Daw Hsu Myat Hlaing']],
                    ['code' => 'McE-32022', 'name' => 'Programmable Logic Controller', 'teachers' => ['Daw Nyein Chan Kyi']],
                    ['code' => 'McE-32034', 'name' => 'Material Science and Strength of Material II', 'teachers' => ['Daw May Thazin Aung']],
                ],
            ],
            [
                'year' => 4,
                'name' => 'Fourth Year',
                // Sheet: Fourth Year IVME, Family - Daw Mya San Yin
                'subjects' => [
                    ['code' => 'E-42011', 'name' => 'English', 'teachers' => ['Daw San Thidar']],
                    ['code' => 'EM-42008', 'name' => 'Engineering Mathematics VIII', 'teachers' => ['Daw Khaing Khaing Htun']],
                    ['code' => 'McE-42025', 'name' => 'Theory of Machines II', 'teachers' => ['Daw Nyein Chan Kyi']],
                    ['code' => 'McE-42035', 'name' => 'Design of Machine Elements II', 'teachers' => ['Daw Myo Myo Zin']],
                    ['code' => 'McE-42017', 'name' => 'Modelling and Control', 'teachers' => ['Daw Mya San Yin']],
                    ['code' => 'McE-42026', 'name' => 'Power Electronic II', 'teachers' => ['Daw Aye Aye Moe']],
                ],
            ],
            [
                'year' => 5,
                'name' => 'Fifth Year',
                // Sheet: Fifth Year VMC, Family - Daw Nyein Chan Kyi
                'subjects' => [
                    ['code' => 'McE-52021', 'name' => 'Robotic Analysis II', 'teachers' => ['Daw Zaw Zaw Zin Phyu', 'Daw May Thazin Aung']],
                    ['code' => 'McE-52039', 'name' => 'Industrial Automation II', 'teachers' => ['Daw Ei Wai Phyo', 'Daw Hsu Myat Hlaing']],
                    ['code' => 'McE-52051', 'name' => 'Machine Vision II', 'teachers' => ['Dr. Hnin Wai Wai Hlaing', 'Daw Nyein Chan Kyi']],
                    ['code' => 'McE-52027', 'name' => 'Fuzzy Logic II', 'teachers' => ['Dr. Saint Saint Pyone', 'Daw Mya San Yin']],
                    ['code' => 'McE-52017', 'name' => 'Modern Control System II', 'teachers' => ['Daw Nu Nu Wai', 'Daw Mya San Yin']],
                    ['code' => 'McE-52029', 'name' => 'Microprocessor And Microcontroller II', 'teachers' => ['Daw Swe Zin Kyaw', 'Daw Aye Aye Moe']],
                    ['code' => 'McE-52018', 'name' => 'Industrial Management II', 'teachers' => ['Daw Aye Thaint Thaint Kyaw', 'Daw Hsu Myat Hlaing']],
                    ['code' => 'McE-52062', 'name' => 'Integrated Design Project', 'teachers' => ['Daw Nyein Chan Kyi']],
                ],
            ],
        ];
    }

    /** @return array<int, array{name: string, position?: string, phone?: string}> */
    private function metTeachers(): array
    {
        return [
            ['name' => 'Daw Khin Moe Win'],
            ['name' => 'U Thet Lwin Aung'],
            ['name' => 'U Si Thu Toe'],
            ['name' => 'U Khaing Kyaw Htun'],
            ['name' => 'U Naing Naing Aung'],
        ];
    }

    /** @return array<int, array{year: int, name: string, subjects: array}> */
    private function metCurriculum(): array
    {
        return [
            [
                'year' => 1,
                'name' => 'First Year',
                // Sheet: First Year I Met,
                'subjects' => [
                    ['code' => 'M-2001', 'name' => 'Myanmar', 'teachers' => ['Daw Khin Khin Aye']],
                    ['code' => 'E-2011', 'name' => 'English', 'teachers' => ['Daw Khaing Mi Mi Htun']],
                    ['code' => 'EM-1001', 'name' => 'Engineering Mathematics I (Retake)', 'teachers' => ['Daw Aye Thuzar Soe']],
                    ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['Daw Aye Thuzar Soe']],
                    ['code' => 'ME-2002', 'name' => 'Industrial Safety and Workshop Practices', 'teachers' => ['Daw Ei Yadanar Phyo']],
                    ['code' => 'EPh-2001', 'name' => 'Engineering Physics', 'teachers' => ['Daw Zar Zar Win', 'Daw Wint War Oo']],
                ],
            ],
            [
                'year' => 2,
                'name' => 'Second Year',
                // Sheet: Second Year II Met
                'subjects' => [
                    ['code' => 'E-4032', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                    ['code' => 'EM-3002', 'name' => 'Engineering Mathematics III (Retake)', 'teachers' => ['Daw Myint Myint Nwee']],
                    ['code' => 'EM-4012', 'name' => 'Engineering Mathematics IV', 'teachers' => ['Daw Myint Myint Nwee']],
                    ['code' => 'EG-4014', 'name' => 'Mineralogy and Petrology For Metallurgical Engineer', 'teachers' => ['Daw Hnin Hnin Shwe']],
                    ['code' => 'ME-4015', 'name' => 'Engineering Mechanics', 'teachers' => ['Daw Su Nandar Aung']],
                    ['code' => 'Met-4012', 'name' => 'Metallurgy and Materials Kinetic', 'teachers' => ['Daw Khin Moe Win']],
                    ['code' => 'Met-4010', 'name' => 'Metallurgical Heat Transfer', 'teachers' => ['U Thet Lwin Aung']],
                    ['code' => 'Met-4000', 'name' => 'Fluid Flow', 'teachers' => ['Daw Khin Moe Win']],
                ],
            ],
            [
                'year' => 3,
                'name' => 'Third Year',
                // Sheet: Third Year III Met
                'subjects' => [
                    ['code' => 'E-32011', 'name' => 'English', 'teachers' => ['Daw Thwe Thwe Maw']],
                    ['code' => 'EM-32006', 'name' => 'Engineering Mathematics VI', 'teachers' => ['Daw Khaing Khaing Htun']],
                    ['code' => 'ME-32014', 'name' => 'Strength of Materials II', 'teachers' => ['Daw Ei Yadanar Phyo']],
                    ['code' => 'Met-32012', 'name' => 'Mineral Processing II', 'teachers' => ['U Thet Lwin Aung']],
                    ['code' => 'Met-32015', 'name' => 'Metallurgical Unit Operation II', 'teachers' => ['U Thet Lwin Aung']],
                    ['code' => 'Met-32044', 'name' => 'Mechanical Behaviour', 'teachers' => ['U Si Thu Toe']],
                    ['code' => 'EG-32004', 'name' => 'Mineralogy and Petrology For Metallurgical Engineer II', 'teachers' => ['Daw Hnin Hnin Shwe']],
                ],
            ],
            [
                'year' => 4,
                'name' => 'Fourth Year',
                // Sheet: Fourth Year IV Met
                'subjects' => [
                    ['code' => 'E-42011', 'name' => 'English', 'teachers' => ['Daw San Thidar']],
                    ['code' => 'EM-42008', 'name' => 'Engineering Mathematics VIII', 'teachers' => ['Daw Ank Phyu Win']],
                    ['code' => 'Met-42016', 'name' => 'Extractive Metallurgy II', 'teachers' => ['U Si Thu Toe']],
                    ['code' => 'Met-42031', 'name' => 'Metallurgical Thermodynamics', 'teachers' => ['Daw Khin Moe Win']],
                    ['code' => 'Met-42023', 'name' => 'Material Science II', 'teachers' => ['Daw Khin Moe Win']],
                    ['code' => 'Met-42033', 'name' => 'Principles of Physical Metallurgy II', 'teachers' => ['U Naing Naing Aung']],
                    ['code' => 'Met-42014', 'name' => 'Foundry Technology II', 'teachers' => ['U Khaing Kyaw Htun']],
                ],
            ],
            [
                'year' => 5,
                'name' => 'Fifth Year',
                // Sheet: Fifth Year V Met
                'subjects' => [
                    ['code' => 'Met-52022', 'name' => 'Industrial Management II', 'teachers' => ['U Thet Lwin Aung']],
                    ['code' => 'Met-52016', 'name' => 'Nonferrous & Ferrous Metallurgy II', 'teachers' => ['U Si Thu Toe']],
                    ['code' => 'Met-52033', 'name' => 'Nanomaterials and Materials Technology', 'teachers' => ['U Thet Lwin Aung']],
                    ['code' => 'Met-52061', 'name' => 'Corrosion Engineering II', 'teachers' => ['U Naing Naing Aung']],
                    ['code' => 'Met-52041', 'name' => 'Fracture Mechanics & Failure Analysis', 'teachers' => ['U Thet Lwin Aung']],
                    ['code' => 'Met-52018', 'name' => 'Metallurgical Engineering Project', 'teachers' => ['U Naing Naing Aung', 'U Thet Lwin Aung']],
                ],
            ],
        ];
    }

    /** @return array<int, array{name: string, position?: string, phone?: string}> */
    private function ntTeachers(): array
    {
        return [
            ['name' => 'Dr. Cho Cho Thein', 'position' => 'Head of Department / Professor'],
            ['name' => 'Dr. Saw Thantar'],
            ['name' => 'U Kyaw Zin Htet'],
            ['name' => 'U Thura Oo'],
            ['name' => 'Dr Myat Mon Aye'],
            ['name' => 'Dr. Khaing Zaw Oo'],
            ['name' => 'Dr. Min Min Zaw'],
        ];
    }

    /** @return array<int, array{year: int, name: string, subjects: array}> */
    private function ntCurriculum(): array
    {
        return [
            [
                'year' => 1,
                'name' => 'First Year',
                // Sheet: First Year I NT
                'subjects' => [
                    ['code' => 'M-2001', 'name' => 'Myanmar', 'teachers' => ['Daw Khin Khin Aye']],
                    ['code' => 'EPh-2001', 'name' => 'Engineering Physics', 'teachers' => ['Daw Zar Zar Win', 'Daw Wint War Oo']],
                    ['code' => 'E-2011', 'name' => 'English', 'teachers' => ['Daw Khaing Mi Mi Tun']],
                    ['code' => 'EM-1001', 'name' => 'Engineering Mathematics I (Retake)', 'teachers' => ['Daw Aye Thuzar Soe']],
                    ['code' => 'NE-2021', 'name' => 'Introduction to Nuclear Physics', 'teachers' => ['Dr. Cho Cho Thein']],
                ],
            ],
            [
                'year' => 2,
                'name' => 'Second Year',
                // Sheet: Second Year II NT
                'subjects' => [
                    ['code' => 'E-4032', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                    ['code' => 'NE-4032', 'name' => 'Heat And Thermodynamics', 'teachers' => ['Dr. Myat Mon Aye']],
                    ['code' => 'NE-4042', 'name' => 'Thermal Hydraulic', 'teachers' => ['U Thura Oo']],
                    ['code' => 'Met-4014', 'name' => 'Engineering Materials', 'teachers' => ['U Khaing Kyaw Htun']],
                    ['code' => 'EM-3002', 'name' => 'Engineering Mathematics III (Retake)', 'teachers' => ['Daw Myint Myint Nwe']],
                    ['code' => 'NE-4041', 'name' => 'Fundamental of Nuclear Technology', 'teachers' => ['Dr. Saw Thantar']],
                    ['code' => 'NE-4023', 'name' => 'Electronic Devices and Circuits:Analog', 'teachers' => ['U Kyaw Zin Htet']],
                    ['code' => 'EM-4012', 'name' => 'Engineering Mathematics IV', 'teachers' => ['Daw Myint Myint Nwe']],
                ],
            ],
            [
                'year' => 3,
                'name' => 'Third Year',
                // Sheet: Third Year III NT
                'subjects' => [
                    ['code' => 'E-32011', 'name' => 'English', 'teachers' => ['U Khin Maung Nyunt']],
                    ['code' => 'EM-32006', 'name' => 'Engineering Mathematics VI', 'teachers' => ['Daw Myint Myint Nwe']],
                    ['code' => 'Met-32023', 'name' => 'Concepts of Material Sciences', 'teachers' => ['U Khaing Kyaw Tun']],
                    ['code' => 'NE-32022', 'name' => 'Introduction to Nuclear Concepts for Engineers', 'teachers' => ['U Kyaw Zin Htet']],
                    ['code' => 'NE-32023', 'name' => 'Thermal Hydraulic', 'teachers' => ['U Thura Oo']],
                    ['code' => 'NE-32031', 'name' => 'Electronic Devices and Circuits:Analog', 'teachers' => ['U Kyaw Zin Htet']],
                ],
            ],
            [
                'year' => 4,
                'name' => 'Fourth Year',
                // Sheet: Fourth Year IV NT
                'subjects' => [
                    ['code' => 'E-42011', 'name' => 'English', 'teachers' => ['Daw San Thidar']],
                    ['code' => 'EM-42008', 'name' => 'Engineering Mathematics VIII', 'teachers' => ['Daw Ank Phyu Win']],
                    ['code' => 'NE-42013', 'name' => 'Radiation Protection and Radiation Shielding', 'teachers' => ['Dr. Cho Cho Thein']],
                    ['code' => 'NE-42032', 'name' => 'Radiation Detection and Measurement', 'teachers' => ['Dr. Saw Thantar']],
                    ['code' => 'NE-42033', 'name' => 'Electronic Devices and Circuits: Digital', 'teachers' => ['Dr. Khaing Zaw Oo']],
                    ['code' => 'NE-42024', 'name' => 'Introduction to Reactor Engineering', 'teachers' => ['Dr. Min Min Zaw']],
                ],
            ],
            [
                'year' => 5,
                'name' => 'Fifth Year',
                // Sheet: Fifth Year V NE
                'subjects' => [
                    ['code' => 'NE-52014', 'name' => 'Radioactive Waste Management', 'teachers' => ['Dr. Myat Mon Aye']],
                    ['code' => 'NE-52034', 'name' => 'Radiation Spectrometry and Counting Statics', 'teachers' => ['Dr. Saw Thantar']],
                    ['code' => 'NE-52025', 'name' => 'Reactor Engineering', 'teachers' => ['Dr. Min Min Zaw']],
                    ['code' => 'NE-52026', 'name' => 'Math CAD', 'teachers' => ['Dr. Khaing Zaw Oo']],
                    ['code' => 'NE-52035', 'name' => 'Introduction to X ray Spectroscopy', 'teachers' => ['Dr. Cho Cho Thein']],
                ],
            ],
        ];
    }

    /** @return array<int, array{name: string, position?: string, phone?: string}> */
    private function bioTTeachers(): array
    {
        return [
            ['name' => 'Dr. Khaing Nwe Soe', 'position' => 'Head of Department / Professor'],
            ['name' => 'Daw Lae Yin Win'],
            ['name' => 'Daw Kyi Kyi Khaing'],
            ['name' => 'Dr. Wah Wah Than'],
            ['name' => 'U Thet Lwin Moe'],
            ['name' => 'Daw Soe Soe Aye'],
            ['name' => 'Dr. Thu Thu Naing Aung'],
            ['name' => 'Daw Thant Thant Soe'],
            ['name' => 'Dr. Tin Myat Myat Soe'],
            ['name' => 'Daw Myint Myint Win'],
        ];
    }

    /** @return array<int, array{year: int, name: string, subjects: array}> */
    private function bioTCurriculum(): array
    {
        return [
            [
                'year' => 1,
                'name' => 'First Year',
                // Sheet: First Year I BioT
                'subjects' => [
                    ['code' => 'M-2001', 'name' => 'Myanmar', 'teachers' => ['Daw Khin Khin Aye']],
                    ['code' => 'E-2011', 'name' => 'English', 'teachers' => ['Daw Khaing Mi Mi Htun']],
                    ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['Daw Aye Thuzar Soe']],
                    ['code' => 'EM-2011', 'name' => 'Engineering Mathematics II', 'teachers' => ['Daw Aye Thuzar Soe']],
                    ['code' => 'EPh-2001', 'name' => 'Engineering Physics', 'teachers' => ['Daw Zar Zar Win', 'Daw Wint War Oo']],
                    ['code' => 'BioT-2011', 'name' => 'Microbiology', 'teachers' => ['Daw Kyi Kyi Khaing']],
                    ['code' => 'BioT-2015', 'name' => 'Introduction to Biotechnology', 'teachers' => ['Daw Lae Yin Win']],
                ],
            ],
            [
                'year' => 2,
                'name' => 'Second Year',
                // Sheet: Second Year II BioT
                'subjects' => [
                    ['code' => 'E-4011', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                    ['code' => 'EM-3002', 'name' => 'Engineering Mathematics III (Retake)', 'teachers' => ['Daw Myint Myint Nwe']],
                    ['code' => 'EM-4012', 'name' => 'Engineering Mathematics IV', 'teachers' => ['Daw Myint Myint Nwe']],
                    ['code' => 'BioT-4012', 'name' => 'Cell Biology', 'teachers' => ['Daw Lae Yin Win']],
                    ['code' => 'BioT-4013', 'name' => 'Molecular Biology II', 'teachers' => ['Dr. Thu Thu Naing Aung']],
                    ['code' => 'BioT-4022', 'name' => 'Organic Chemistry', 'teachers' => ['U Thet Lwin Moe', 'Daw Soe Soe Aye']],
                    ['code' => 'BioT-4014', 'name' => 'Principle of Genetic', 'teachers' => ['Dr. Wah Wah Than']],
                ],
            ],
            [
                'year' => 3,
                'name' => 'Third Year',
                // Sheet: Third Year III BioT
                'subjects' => [
                    ['code' => 'E-32011', 'name' => 'English', 'teachers' => ['Daw Thwe Thwe Maw']],
                    ['code' => 'EM-32006', 'name' => 'Engineering Mathematics VI', 'teachers' => ['Daw Myint Myint Nwe']],
                    ['code' => 'BioT-32012', 'name' => 'Biochemistry', 'teachers' => ['Daw Soe Soe Aye', 'U Thet Lwin Moe']],
                    ['code' => 'BioT-32032', 'name' => 'Analytical Chemistry', 'teachers' => ['Daw Soe Soe Aye', 'U Thet Lwin Moe']],
                    ['code' => 'BioT-32052', 'name' => 'Bioprocess Engineering', 'teachers' => ['Daw Thant Thant Soe']],
                    ['code' => 'BioT-32042', 'name' => 'Genetic Engineering', 'teachers' => ['Dr. Thu Thu Naing Aung']],
                    ['code' => 'BioT-32022', 'name' => 'Microbiology', 'teachers' => ['Dr. Wah Wah Than']],
                ],
            ],
            [
                'year' => 4,
                'name' => 'Fourth Year',
                // Sheet: Fourth Year IV BioT
                'subjects' => [
                    ['code' => 'E-42011', 'name' => 'English', 'teachers' => ['Daw San Thidar']],
                    ['code' => 'BioT-42061', 'name' => 'Research Methodology', 'teachers' => ['Dr. Thu Thu Naing Aung']],
                    ['code' => 'BioT-42023', 'name' => 'Microbiology', 'teachers' => ['Daw Myint Myint Win']],
                    ['code' => 'BioT-42053', 'name' => 'Fermentation Technology', 'teachers' => ['U Thet Lwin Moe', 'Daw Soe Soe Aye']],
                    ['code' => 'BioT-42013', 'name' => 'Enzymology', 'teachers' => ['Dr. Khaing Nwe Soe']],
                    ['code' => 'BioT-42062', 'name' => 'Biostatistics', 'teachers' => ['Dr. Tin Myat Myat Soe']],
                    ['code' => 'BioT-42063', 'name' => 'Biosafety & Bioethics', 'teachers' => ['Daw Thant Thant Soe']],
                ],
            ],
            [
                'year' => 6,
                'name' => 'Master (M.E)',
                // Sheet: Master ME BioT
                'subjects' => [
                    ['code' => 'E-62011', 'name' => 'English', 'teachers' => ['U Aung Kyaw Myint']],
                    ['code' => 'BioT-62025', 'name' => 'Microbiology & Biotechnology', 'teachers' => ['Daw Myint Myint Win']],
                    ['code' => 'BioT-62016', 'name' => 'Advanced Molecular Genetic & Cell Biology', 'teachers' => ['Dr. Wah Wah Than']],
                    ['code' => 'BioT-62065', 'name' => 'Biostatistics', 'teachers' => ['Dr. Tin Myat Myat Soe']],
                    ['code' => 'BioT-62054', 'name' => 'Pollution, Waste Management & Bioremediation', 'teachers' => ['Daw Kyi Kyi Khaing']],
                    ['code' => 'BioT-62017', 'name' => 'Enzymology', 'teachers' => ['U Thet Lwin Moe', 'Daw Soe Soe Aye']],
                    ['code' => 'BioT-62055', 'name' => 'Bioprocess Engineering Principles', 'teachers' => ['Dr. Khaing Nwe Soe']],
                ],
            ],
        ];
    }
}

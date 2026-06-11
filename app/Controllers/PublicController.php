<?php



declare(strict_types=1);



final class PublicController

{

    private PesquisaRepository $repo;



    public function __construct(private readonly array $tenant)

    {

        $this->repo = new PesquisaRepository(Database::connection());

    }



    public function home(): void

    {

        $survey = $this->repo->findActiveSurvey((int) $this->tenant['id']);



        View::render('home', [

            'title' => 'Pesquisa Psicossocial',

            'tenant' => $this->tenant,

            'survey' => $survey,

        ]);

    }



    public function respond(): void

    {

        $context = $this->resolveSurveyContext();

        if ($context === null) {

            flash('info', 'Não há pesquisa aberta no momento.');

            redirect(tenant_path());

        }



        $survey = $context['survey'];

        $versao = (int) ($survey['versao'] ?? 1);

        $questions = $this->repo->listQuestions((int) $survey['id'], $versao);



        View::render('respond', [

            'title' => 'Responder pesquisa',

            'tenant' => $this->tenant,

            'survey' => $survey,

            'questions' => $questions,

            'csrf' => Security::csrfToken(),

            'formErrors' => errors(),

            'conviteToken' => $context['convite_token'] ?? '',

        ]);

    }



    public function submitRespond(): void

    {

        if (!Security::validateCsrf($_POST['_csrf'] ?? null)) {

            back_with_errors(['_form' => 'Sessão expirada. Tente novamente.'], tenant_path('/responder'));

        }



        $context = $this->resolveSurveyContext(true);

        if ($context === null) {

            back_with_errors(['_form' => 'Esta pesquisa não está mais disponível.'], tenant_path());

        }



        $survey = $context['survey'];

        $versao = (int) ($survey['versao'] ?? 1);

        $questions = $this->repo->listQuestions((int) $survey['id'], $versao);

        $answers = [];

        $errors = [];



        foreach ($questions as $question) {

            $qid = (int) $question['id'];

            $tipo = (string) $question['tipo'];

            $required = (int) ($question['obrigatoria'] ?? 1) === 1;

            $field = 'q_' . $qid;



            if (in_array($tipo, ['E', 'B', 'S'], true)) {

                $value = $_POST[$field] ?? '';

                if ($required && $value === '') {

                    $errors[$field] = 'Selecione uma opção.';

                    continue;

                }

                if ($value !== '') {

                    $answers[$qid] = ['texto' => null, 'numerico' => (float) $value, 'opcoes' => [(int) $value]];

                }

                continue;

            }



            if ($tipo === 'M') {

                $values = $_POST[$field] ?? [];

                if (!is_array($values)) {

                    $values = $values !== '' ? [(string) $values] : [];

                }

                $values = array_values(array_filter(array_map('strval', $values)));

                if ($required && !$values) {

                    $errors[$field] = 'Selecione ao menos uma opção.';

                    continue;

                }

                if ($values) {

                    $numeric = array_map('floatval', $values);

                    $answers[$qid] = [

                        'texto' => implode(', ', $values),

                        'numerico' => count($numeric) ? array_sum($numeric) / count($numeric) : null,

                        'opcoes' => array_map('intval', $values),

                    ];

                }

                continue;

            }



            $value = trim((string) ($_POST[$field] ?? ''));

            if ($required && $value === '') {

                $errors[$field] = 'Preencha este campo.';

                continue;

            }

            if ($value !== '') {

                $answers[$qid] = ['texto' => $value, 'numerico' => null];

            }

        }



        if ($errors) {

            with_old_input($_POST);

            $redirect = tenant_path('/responder');

            if (!empty($context['convite_token'])) {

                $redirect = tenant_path('/responder?token=' . urlencode((string) $context['convite_token']));

            }

            back_with_errors($errors, $redirect);

        }



        if (!$answers) {

            back_with_errors(['_form' => 'Informe ao menos uma resposta.'], tenant_path('/responder'));

        }



        $sessionToken = !empty($context['convite_token'])

            ? (string) $context['convite_token']

            : Security::sessionToken();



        try {

            $this->repo->saveResponses(

                (int) $survey['id'],

                (int) $this->tenant['id'],

                $versao,

                $sessionToken,

                $answers,

                isset($context['convite_id']) ? (int) $context['convite_id'] : null

            );

        } catch (Throwable $e) {

            back_with_errors(['_form' => 'Não foi possível registrar suas respostas. Tente novamente.'], tenant_path('/responder'));

        }



        clear_old_input();

        redirect(tenant_path('/obrigado'));

    }



    public function thankyou(): void

    {

        View::render('thankyou', [

            'title' => 'Obrigado',

            'tenant' => $this->tenant,

        ]);

    }



    public function policy(): void

    {

        View::render('policy', [

            'title' => 'Política de privacidade',

            'tenant' => $this->tenant,

        ]);

    }



    /**

     * @return array{survey: array<string, mixed>, convite_token?: string, convite_id?: int}|null

     */

    private function resolveSurveyContext(bool $fromPost = false): ?array

    {

        $token = trim((string) ($fromPost ? ($_POST['convite_token'] ?? '') : ($_GET['token'] ?? '')));



        if ($token !== '') {

            $convite = $this->repo->findConviteByToken($token, (int) $this->tenant['id']);

            if ($convite === null) {

                return null;

            }



            $survey = $this->repo->findSurveyById((int) $convite['pesquisa_id'], (int) $this->tenant['id']);

            if ($survey === null || !$this->repo->isSurveyOpen($survey)) {

                return null;

            }



            return [

                'survey' => $survey,

                'convite_token' => $token,

                'convite_id' => (int) $convite['id'],

            ];

        }



        $survey = $this->repo->findActiveSurvey((int) $this->tenant['id']);

        if ($survey === null) {

            return null;

        }



        return ['survey' => $survey];

    }

}


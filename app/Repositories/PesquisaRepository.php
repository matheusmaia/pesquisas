<?php

declare(strict_types=1);

final class PesquisaRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function findCompanyBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM empresas WHERE slug = :slug AND status = "ativo" LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findActiveSurvey(int $empresaId): ?array
    {
        if ($empresaId <= 0) {
            return null;
        }

        $today = date('Y-m-d');
        $stmt = $this->db->prepare(
            'SELECT *
             FROM pesquisas
             WHERE empresa_id = :empresa_id
               AND status IN ("publicada", "aberta")
               AND (data_inicio IS NULL OR data_inicio <= :hoje1)
               AND (data_fim IS NULL OR data_fim >= :hoje2)
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute([
            'empresa_id' => $empresaId,
            'hoje1' => $today,
            'hoje2' => $today,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listQuestions(int $pesquisaId, int $versao): array
    {
        $stmt = $this->db->prepare(
            'SELECT sp.id, sp.enunciado, sp.tipo, sp.obrigatoria, sp.grupo_nome
             FROM pesquisa_snapshot_pergunta sp
             WHERE sp.pesquisa_id = :pesquisa_id AND sp.versao = :versao
             ORDER BY sp.ordem ASC, sp.id ASC'
        );
        $stmt->execute(['pesquisa_id' => $pesquisaId, 'versao' => $versao]);
        $questions = $stmt->fetchAll() ?: [];

        foreach ($questions as &$question) {
            $question['opcoes'] = $this->listOptions((int) $question['id']);
        }
        unset($question);

        return $questions;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listOptions(int $snapshotPerguntaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, ordem, rotulo, valor_numerico, pontuacao
             FROM pesquisa_snapshot_opcao
             WHERE snapshot_pergunta_id = :id
             ORDER BY ordem ASC, id ASC'
        );
        $stmt->execute(['id' => $snapshotPerguntaId]);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @param array<int, array{texto?: ?string, numerico?: ?float, opcoes?: list<int>}> $answers keyed by snapshot_pergunta_id
     */
    public function saveResponses(int $pesquisaId, int $empresaId, int $versao, string $token, array $answers): void
    {
        $tokenHash = hash('sha256', $token);

        $this->db->beginTransaction();
        try {
            $stmtSessao = $this->db->prepare(
                'INSERT INTO pesquisa_resposta_sessao (pesquisa_id, empresa_id, versao, token_hash, created_at)
                 VALUES (:pesquisa_id, :empresa_id, :versao, :token_hash, NOW())'
            );
            $stmtSessao->execute([
                'pesquisa_id' => $pesquisaId,
                'empresa_id' => $empresaId,
                'versao' => $versao,
                'token_hash' => $tokenHash,
            ]);
            $sessaoId = (int) $this->db->lastInsertId();

            $stmtResp = $this->db->prepare(
                'INSERT INTO pesquisa_respostas
                    (sessao_id, snapshot_pergunta_id, pergunta_catalogo_id, valor_texto, valor_numerico, valor_opcoes, created_at)
                 SELECT :sessao_id, sp.id, sp.pergunta_catalogo_id, :valor_texto, :valor_numerico, :valor_opcoes, NOW()
                 FROM pesquisa_snapshot_pergunta sp
                 WHERE sp.id = :snapshot_pergunta_id AND sp.pesquisa_id = :pesquisa_id
                 LIMIT 1'
            );

            foreach ($answers as $snapshotPerguntaId => $payload) {
                $opcoesJson = null;
                if (!empty($payload['opcoes']) && is_array($payload['opcoes'])) {
                    $opcoesJson = json_encode(array_values($payload['opcoes']), JSON_UNESCAPED_UNICODE);
                }
                $stmtResp->execute([
                    'sessao_id' => $sessaoId,
                    'snapshot_pergunta_id' => (int) $snapshotPerguntaId,
                    'pesquisa_id' => $pesquisaId,
                    'valor_texto' => $payload['texto'] ?? null,
                    'valor_numerico' => $payload['numerico'] ?? null,
                    'valor_opcoes' => $opcoesJson,
                ]);
            }

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function countSubmissions(int $pesquisaId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pesquisa_resposta_sessao WHERE pesquisa_id = :pesquisa_id');
        $stmt->execute(['pesquisa_id' => $pesquisaId]);

        return (int) $stmt->fetchColumn();
    }
}

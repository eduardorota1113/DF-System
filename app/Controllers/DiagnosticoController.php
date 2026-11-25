
<?php

require_once __DIR__ . '/../DAO/DiagnosticoDAO.php';

class DiagnosticoController
{
    private const CATEGORIAS = ['eletrica', 'hidraulica', 'eletrodomesticos', 'climatizacao', 'outros'];
    private const PERIGOS = ['sim', 'nao', 'nao_sei'];

    private DiagnosticoDAO $diagnosticoDAO;

    public function __construct(PDO $conexao)
    {
        $this->diagnosticoDAO = new DiagnosticoDAO($conexao);
    }

    public function listar(): array
    {
        return $this->diagnosticoDAO->listar();
    }

    public function buscarDetalhes(int $id): ?array
    {
        return $this->diagnosticoDAO->buscarDetalhes($id);
    }

    public function listarRelatos(): array
    {
        return $this->diagnosticoDAO->listarRelatos();
    }

    public function salvarRelato(
        ?int $id,
        int $idUsuario,
        string $descricao,
        string $categoria,
        string $perigo
    ): ?array {
        if (!in_array($categoria, self::CATEGORIAS, true)) {
            throw new InvalidArgumentException('Selecione a área do problema.');
        }
        if (!in_array($perigo, self::PERIGOS, true)) {
            throw new InvalidArgumentException('Informe se há algum sinal de perigo.');
        }
        $descricao = trim($descricao);
        $tamanho = mb_strlen($descricao, 'UTF-8');
        $minimo = $id === null ? 15 : 1;
        $maximo = $id === null ? 1200 : 5000;
        if ($tamanho < $minimo || $tamanho > $maximo) {
            throw new InvalidArgumentException("Use entre $minimo e $maximo caracteres na descrição.");
        }

        $registro = new Diagnostico($id, $idUsuario, $descricao, null, $categoria, $perigo);
        if ($id === null) {
            $id = $this->diagnosticoDAO->cadastrar($registro);
        } else {
            $this->diagnosticoDAO->atualizar($registro);
        }
        return $this->diagnosticoDAO->buscarDetalhes($id);
    }

    public function importarHistorico(int $idUsuario, string $fonte, array $relatos): array
    {
        if (!in_array($fonte, ['interface', 'integrado'], true)) {
            throw new InvalidArgumentException('A origem do histórico não foi reconhecida.');
        }
        if (!array_is_list($relatos) || count($relatos) < 1 || count($relatos) > 100) {
            throw new InvalidArgumentException('Selecione um histórico com 1 a 100 relatos.');
        }
        $dados = [];
        foreach ($relatos as $indice => $relato) {
            if (!is_array($relato)) {
                throw new InvalidArgumentException('O arquivo contém um relato inválido.');
            }
            $id = $relato['id'] ?? null;
            $descricao = $relato['description'] ?? null;
            $categoria = $relato['category'] ?? null;
            $perigo = $relato['danger'] ?? null;
            $categoriaValida = in_array($categoria, self::CATEGORIAS, true)
                || ($fonte === 'integrado' && $categoria === null);
            if (!is_string($id) || $id === '' || strlen($id) > 800 ||
                !is_string($descricao) || trim($descricao) === '' || mb_strlen($descricao, 'UTF-8') > 5000 ||
                !$categoriaValida ||
                ($perigo !== null && !in_array($perigo, self::PERIGOS, true))
            ) {
                throw new InvalidArgumentException('Confira o relato ' . ($indice + 1) . ' do histórico de origem.');
            }

            $data = null;
            $dataExibida = null;
            $dataOriginal = $relato['date'] ?? null;
            if (is_string($dataOriginal) &&
                preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?(?:Z|[+-]\d{2}:\d{2})$/D', $dataOriginal)
            ) {
                $data = new DateTimeImmutable($dataOriginal);
                $dataExibida = $data->format(DATE_ATOM);
                $data = $data->setTimezone(new DateTimeZone('UTC'));
            } elseif (is_string($dataOriginal) &&
                preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $dataOriginal)
            ) {
                $data = new DateTimeImmutable($dataOriginal);
                $dataExibida = $data->format('Y-m-d H:i:s');
            }
            $contexto = ['data_original' => $dataExibida];
            if (is_string($relato['location'] ?? null)) {
                $contexto['location'] = mb_substr($relato['location'], 0, 500, 'UTF-8');
            }
            foreach ([
                'since' => ['hoje', 'dias', 'semanas', 'nao_sei'],
                'frequency' => ['constante', 'as_vezes', 'uma_vez', 'nao_sei'],
            ] as $campo => $opcoes) {
                $valor = $relato[$campo] ?? null;
                if (in_array($valor, $opcoes, true)) {
                    $contexto[$campo] = $valor;
                }
            }
            $dados[] = [
                ':descricao' => $descricao,
                ':categoria' => $categoria,
                ':perigo' => $perigo,
                ':data' => $data?->format('Y-m-d H:i:s'),
                ':contexto' => json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                ':origem' => hash('sha256', "$fonte:$idUsuario:$id"),
            ];
        }
        return $this->diagnosticoDAO->importarRelatos($idUsuario, $dados);
    }

public function cadastrar(int $idUsuario, string $descricao): int
{
    if ($idUsuario <= 0) {
        throw new InvalidArgumentException(
            "ID do usuário inválido."
        );
    }

    $descricao = trim($descricao);

    if ($descricao === '') {
        throw new InvalidArgumentException(
            "A descrição do problema é obrigatória."
        );
    }

    $diagnostico = new Diagnostico(
        null,
        $idUsuario,
        $descricao
    );

    return $this->diagnosticoDAO->cadastrar($diagnostico);
}

public function atualizar(
    int $idDiagnostico,
    int $idUsuario,
    string $descricao
): bool {
    if ($idDiagnostico <= 0 || $idUsuario <= 0) {
        throw new InvalidArgumentException(
            "ID do diagnóstico ou do usuário inválido."
        );
    }

    $descricao = trim($descricao);

    if ($descricao === '') {
        throw new InvalidArgumentException(
            "A descrição do problema é obrigatória."
        );
    }

    $diagnostico = new Diagnostico(
        $idDiagnostico,
        $idUsuario,
        $descricao
    );

    return $this->diagnosticoDAO->atualizar($diagnostico);
}

public function excluir(int $idDiagnostico): bool
{
    if ($idDiagnostico <= 0) {
        throw new InvalidArgumentException(
            "ID do diagnóstico inválido."
        );
    }

    return $this->diagnosticoDAO->excluir($idDiagnostico);
}

}

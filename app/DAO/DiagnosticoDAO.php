
<?php

require_once __DIR__ . '/../Models/Diagnostico.php';
require_once __DIR__ . '/RepositorioInterface.php';

class DiagnosticoDAO implements RepositorioInterface
{
    private const CONSULTA_RELATO = "SELECT d.id_diagnostico, d.id_usuario, d.descricao,
                   d.data_diagnostico, d.categoria, d.perigo, d.contexto_importado,
                   u.nome AS nome_usuario
            FROM diagnostico d
            INNER JOIN usuario u ON u.id_usuario = d.id_usuario";

    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function cadastrar(Diagnostico $diagnostico): int
    {
        $sql = "INSERT INTO diagnostico (id_usuario, descricao, categoria, perigo)
                VALUES (:id_usuario, :descricao, :categoria, :perigo)";

        $comando = $this->conexao->prepare($sql);

        $comando->execute([
            ':id_usuario' => $diagnostico->getIdUsuario(),
            ':descricao' => $diagnostico->getDescricao(),
            ':categoria' => $diagnostico->getCategoria(),
            ':perigo' => $diagnostico->getPerigo()
        ]);

        return (int) $this->conexao->lastInsertId();
    }

    public function listar(): array
    {
        $sql = "SELECT id_diagnostico,
                       id_usuario,
                       descricao,
                       data_diagnostico,
                       categoria,
                       perigo
                FROM diagnostico
                ORDER BY id_diagnostico";

        $comando = $this->conexao->query($sql);
        $diagnosticos = [];

        foreach ($comando->fetchAll() as $registro) {
            $diagnosticos[] = $this->montarDiagnostico($registro);
        }

        return $diagnosticos;
    }

    public function atualizar(Diagnostico $diagnostico): bool
    {
        $id = $diagnostico->getIdDiagnostico();

        if ($id === null || $id <= 0) {
            throw new InvalidArgumentException(
                "ID do diagnóstico inválido."
            );
        }

        $campos = 'id_usuario = :id_usuario, descricao = :descricao';
        $parametros = [
            ':id_usuario' => $diagnostico->getIdUsuario(),
            ':descricao' => $diagnostico->getDescricao(),
            ':id' => $id
        ];
        if ($diagnostico->getCategoria() !== null) {
            $campos .= ', categoria = :categoria, perigo = :perigo';
            $parametros[':categoria'] = $diagnostico->getCategoria();
            $parametros[':perigo'] = $diagnostico->getPerigo();
        }
        $comando = $this->conexao->prepare(
            "UPDATE diagnostico SET $campos WHERE id_diagnostico = :id"
        );
        $comando->execute($parametros);

        return $comando->rowCount() > 0;
    }

    public function buscarDetalhes(int $id): ?array
    {
        $sql = self::CONSULTA_RELATO . ' WHERE d.id_diagnostico = :id';
        $comando = $this->conexao->prepare($sql);
        $comando->execute([':id' => $id]);
        $registro = $comando->fetch(PDO::FETCH_ASSOC);

        if ($registro === false) {
            return null;
        }

        return $this->montarRelato($registro);
    }

    public function listarRelatos(): array
    {
        $comando = $this->conexao->query(self::CONSULTA_RELATO
            . ' ORDER BY d.data_diagnostico DESC, d.id_diagnostico DESC');
        return array_map([$this, 'montarRelato'], $comando->fetchAll(PDO::FETCH_ASSOC));
    }

    public function importarRelatos(int $idUsuario, array $registros): array
    {
        $buscar = $this->conexao->prepare(
            'SELECT id_diagnostico FROM diagnostico WHERE origem_importacao = :origem'
        );
        $inserir = $this->conexao->prepare(
            'INSERT INTO diagnostico
                (id_usuario, descricao, categoria, perigo, data_diagnostico, contexto_importado, origem_importacao)
             VALUES
                (:usuario, :descricao, :categoria, :perigo, COALESCE(:data, CURRENT_TIMESTAMP), :contexto, :origem)'
        );
        $inseridos = 0;
        $this->conexao->beginTransaction();
        try {
            foreach ($registros as $registro) {
                $buscar->execute([':origem' => $registro[':origem']]);
                $existe = $buscar->fetchColumn() !== false;
                $buscar->closeCursor();
                if ($existe) {
                    continue;
                }
                try {
                    $inserir->execute($registro + [':usuario' => $idUsuario]);
                    $inseridos++;
                } catch (PDOException $erro) {
                    if (($erro->errorInfo[1] ?? null) !== 1062) {
                        throw $erro;
                    }
                }
            }
            $this->conexao->commit();
        } catch (Throwable $erro) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            throw $erro;
        }
        return ['importados' => $inseridos, 'repetidos' => count($registros) - $inseridos];
    }

    private function montarRelato(array $registro): array
    {
        return [
            'diagnostico' => $this->montarDiagnostico($registro),
            'nome_usuario' => $registro['nome_usuario'],
            'contexto_importado' => $registro['contexto_importado'],
        ];
    }

    private function montarDiagnostico(array $registro): Diagnostico
    {
        return new Diagnostico(
            (int) $registro['id_diagnostico'],
            (int) $registro['id_usuario'],
            $registro['descricao'],
            $registro['data_diagnostico'],
            $registro['categoria'],
            $registro['perigo']
        );
    }

    public function excluir(int $id): bool
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                "ID do diagnóstico inválido."
            );
        }

        $comando = $this->conexao->prepare(
            "DELETE FROM diagnostico WHERE id_diagnostico = :id"
        );

        $comando->execute([':id' => $id]);

        return $comando->rowCount() > 0;
    }
}

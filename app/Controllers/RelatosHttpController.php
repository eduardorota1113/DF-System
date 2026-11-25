<?php

require_once __DIR__ . '/DiagnosticoController.php';
require_once __DIR__ . '/UsuarioController.php';

class RelatosHttpController
{
    public function responder(string $tokenCsrf): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        $metodo = $_SERVER['REQUEST_METHOD'];
        if ($metodo !== 'GET' && $metodo !== 'POST') {
            header('Allow: GET, POST');
            $this->enviar(['erro' => 'Método não permitido.'], 405);
            return;
        }

        if ($metodo === 'POST' &&
            !hash_equals($tokenCsrf, $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')
        ) {
            $this->enviar(['erro' => 'Sessão expirada. Atualize a página.'], 403);
            return;
        }
        session_write_close();

        try {
            $conexao = Conexao::conectar();
            $diagnosticos = new DiagnosticoController($conexao);
            if ($metodo === 'GET') {
                $usuarios = (new UsuarioController($conexao))->listar();
                $this->enviar([
                    'usuarios' => array_map(static fn (Usuario $usuario) => [
                        'id' => (string) $usuario->getIdUsuario(),
                        'nome' => $usuario->getNome(),
                    ], $usuarios),
                    'registros' => array_map([$this, 'serializar'], $diagnosticos->listarRelatos()),
                ]);
                return;
            }

            $entrada = fopen('php://input', 'rb');
            $corpo = $entrada === false ? false : stream_get_contents($entrada, 3000001);
            if ($entrada !== false) {
                fclose($entrada);
            }
            if (!is_string($corpo) || strlen($corpo) > 3000000) {
                $this->enviar(['erro' => 'O relato excedeu o tamanho permitido.'], 413);
                return;
            }
            $dados = json_decode($corpo, true, 16, JSON_THROW_ON_ERROR);
            if (!is_array($dados)) {
                throw new InvalidArgumentException('Dados inválidos.');
            }

            $acao = $dados['acao'] ?? '';
            if ($acao === 'importar') {
                if (!is_array($dados['registros'] ?? null) || !is_string($dados['fonte'] ?? null)) {
                    throw new InvalidArgumentException('Histórico inválido.');
                }
                $this->enviar($diagnosticos->importarHistorico(
                    $this->idPositivo($dados['userId'] ?? null),
                    $dados['fonte'],
                    $dados['registros']
                ));
                return;
            }
            if ($acao === 'excluir') {
                if (!$diagnosticos->excluir($this->idPositivo($dados['id'] ?? null))) {
                    $this->enviar(['erro' => 'Relato não encontrado.'], 404);
                    return;
                }
                $this->enviar(['ok' => true]);
                return;
            }
            if ($acao !== 'salvar') {
                throw new InvalidArgumentException('Operação inválida.');
            }

            $id = isset($dados['id']) ? $this->idPositivo($dados['id']) : null;
            foreach (['description', 'category', 'danger'] as $campo) {
                if (!is_string($dados[$campo] ?? null)) {
                    throw new InvalidArgumentException('Confira os campos do relato.');
                }
            }
            $relato = $diagnosticos->salvarRelato(
                $id,
                $this->idPositivo($dados['userId'] ?? null),
                $dados['description'],
                $dados['category'],
                $dados['danger']
            );
            if ($relato === null) {
                $this->enviar(['erro' => 'Relato não encontrado.'], 404);
                return;
            }
            $this->enviar(['registro' => $this->serializar($relato)], $id === null ? 201 : 200);
        } catch (JsonException $erro) {
            $this->enviar(['erro' => 'Não foi possível ler os dados enviados.'], 400);
        } catch (InvalidArgumentException $erro) {
            $this->enviar(['erro' => $erro->getMessage()], 422);
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23000') {
                $this->enviar(['erro' => 'Selecione um usuário cadastrado.'], 422);
                return;
            }
            $this->enviar(['erro' => 'Não foi possível consultar ou atualizar o banco.'], 500);
        } catch (Throwable $erro) {
            $this->enviar(['erro' => 'Não foi possível concluir a operação.'], 500);
        }
    }

    private function idPositivo(mixed $valor): int
    {
        $id = is_int($valor) || is_string($valor)
            ? filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;
        if ($id === false) {
            throw new InvalidArgumentException('Identificador inválido.');
        }
        return $id;
    }

    private function serializar(array $relato): array
    {
        $diagnostico = $relato['diagnostico'];
        $contexto = json_decode($relato['contexto_importado'] ?? '{}', true) ?? [];
        return [
            'id' => (string) $diagnostico->getIdDiagnostico(),
            'userId' => (string) $diagnostico->getIdUsuario(),
            'userName' => $relato['nome_usuario'],
            'description' => $diagnostico->getDescricao(),
            'category' => $diagnostico->getCategoria(),
            'danger' => $diagnostico->getPerigo(),
            'date' => array_key_exists('data_original', $contexto)
                ? $contexto['data_original']
                : $diagnostico->getDataDiagnostico(),
            'location' => $contexto['location'] ?? null,
            'since' => $contexto['since'] ?? null,
            'frequency' => $contexto['frequency'] ?? null,
            'demo' => $diagnostico->getCategoria() !== null && $diagnostico->getPerigo() !== null,
        ];
    }

    private function enviar(array $dados, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}

# Documentação do sistema — Dogão Lanches

## O que este projeto faz

Este é um painel interno para um restaurante. Nele, uma pessoa autorizada pode:

- entrar com e-mail e senha;
- consultar pedidos por mesa e o estado de pagamento;
- registrar e atualizar pedidos pela recepção;
- visualizar e alterar os e-mails da equipe no painel;
- administrar os funcionários, caso seja administrador.

## Caminho principal do usuário

```text
public/index.php
        ↓
templates/login.php ──(envia e-mail e senha)──> auth.php
        ↑                                        ↓
        └─────── erro de login             templates/painel.php
                                                    ├── templates/pedidos.php (acesso por perfil)
                                                    ├── atualizar e-mail
                                                    ├── templates/crud.php (somente admin)
                                                    └── logout.php
```

Para acessar diretamente, use `templates/login.php`. O arquivo `public/index.php` apenas redireciona para essa tela.

## Responsabilidade de cada arquivo

| Arquivo | Responsabilidade | Usado no fluxo atual? |
| --- | --- | --- |
| `config/conexao.php` | Define a URL base do projeto e cria a conexão PDO com o MySQL. A variável `$conn` é usada nas consultas ao banco. | Sim |
| `src/Auth.php` | Centraliza sessão, login, logout, validação de e-mail, controle de acesso e hash de senha. | Sim |
| `auth.php` | Recebe o formulário de login, chama `Auth::login()` e envia a recepção diretamente para pedidos; os demais perfis seguem para o painel. | Sim |
| `logout.php` | Chama o logout: limpa os dados da sessão, remove o cookie e redireciona ao login. | Sim |
| `templates/login.php` | Exibe o formulário de acesso. Com sessão válida, envia a recepção aos pedidos e os demais perfis ao painel. | Sim |
| `templates/painel.php` | Tela principal. Exige login, mostra a equipe e permite alterar e-mails. Para pedidos, direciona os perfis permitidos à tela própria. | Sim |
| `templates/pedidos.php` | Tela de pedidos: administrador e recepção cadastram, editam e excluem; gerente consulta. | Sim |
| `src/Pedidos.php` | Cria/atualiza a estrutura da tabela `pedidos` e define os status de pagamento aceitos. | Sim |
| `templates/crud.php` | Área exclusiva de administradores para criar, buscar, editar, ativar/desativar e excluir funcionários. | Sim |
| `public/index.php` | Atalho de entrada que redireciona para o login. | Sim |
| `public/css/style.css` | Estilos da tela de login, painel e cadastro de funcionários, incluindo adaptação para celular. | Sim |
| `mesa.sql` | Estrutura inicial e usuário administrador do banco de dados. | Sim, na instalação |
| `.htaccess` | Bloqueia acesso web direto às pastas `config/` e `src/` e envia alguns cabeçalhos de segurança. | Sim, se o Apache permitir `.htaccess` |
| `templates/index.php` | Redireciona links antigos para `painel.php`. | Sim, como compatibilidade |
| `templates/admin.css` | Folha de estilo antiga, atualmente não carregada. | Não |
| `public/telalogin.css` | Folha de estilo antiga que não é carregada pelas telas atuais. | Não |
| `.vscode/settings.json` | Preferências locais do editor VS Code. Não interfere no sistema em produção. | Não |

## Autenticação e permissões

### Sessão

Depois de um login válido, `src/Auth.php` guarda estes dados em `$_SESSION`:

| Chave | Conteúdo |
| --- | --- |
| `usuario_id` | ID do usuário no banco |
| `usuario` | Nome exibido no sistema |
| `usuario_email` | E-mail do usuário |
| `usuario_role` | Perfil de acesso |

O ID da sessão é renovado após o login para reduzir o risco de fixação de sessão. O cookie da sessão é `httponly` e usa `SameSite=Lax`.

### Perfis

| Valor em `role` | Perfil | Acesso atual |
| --- | --- | --- |
| `1` | Administrador | Painel e cadastro completo de funcionários |
| `2` | Gerente | Painel e consulta de pedidos |
| `4` | Recepção | Entra diretamente em pedidos; consulta, cadastra e edita pedidos |

`Auth::requireLogin()` bloqueia quem não está logado. `Auth::requireAdmin()` usa essa verificação e também bloqueia quem não tem `role = 1`. A tela de pedidos é restrita a administrador, gerente e recepção. Administrador e recepção podem alterar pedidos; gerente apenas consulta.

O perfil antigo `3` (Funcionário) foi removido. Se houver contas antigas com esse valor, um administrador deve editar cada uma e escolher Gerente ou Recepção antes que a pessoa consiga entrar novamente.

## Funcionalidades por tela

### Login — `templates/login.php`

O formulário envia `email` e `senha` por `POST` para `auth.php`. A senha enviada nunca é comparada diretamente com o banco: `Auth::login()` usa `password_verify()` contra `users_login.password_hash`.

### Painel — `templates/painel.php`

Ao abrir, o painel:

1. confirma que existe uma sessão ativa;
2. busca usuários e a quantidade de usuários ativos;
3. mostra o valor dos pedidos pagos no dia e o atalho para pedidos somente para administrador, gerente e recepção.

No painel, a ação `POST` disponível é salvar e-mail (`usuario_id`, `email`), que atualiza `users_login.email`.

### Pedidos — `templates/pedidos.php`

A tela separada controla os pedidos por mesa e mostra os totais acumulados de hoje, separados em pago e a receber. Administrador e recepção podem registrar, editar e excluir pedidos; gerente vê a lista em modo leitura.

| Campo | Finalidade |
| --- | --- |
| Mesa | Número da mesa, entre 1 e 999. |
| Valor | Valor total do pedido. |
| Status de pagamento | `Ainda não pago` ou `Pago`. |

Administrador e gerente veem a opção **Zerar o dia**. Ela exclui todos os pedidos registrados na data atual, pagos e pendentes, apenas depois de validar a senha da conta que está logada.

O número mostrado na lista de pedidos é uma sequência visual diária: começa em `#1` a cada data e, depois de zerar o dia, o próximo pedido volta a aparecer como `#1`. O ID interno do banco permanece único para evitar conflitos entre dias diferentes.

### Cadastro de funcionários — `templates/crud.php`

Somente administradores entram nesta página. Ela trabalha exclusivamente com `users_login`.

| Ação | O que acontece |
| --- | --- |
| Cadastrar | Valida nome, e-mail, senha e perfil; verifica e-mail repetido; salva a senha com hash. |
| Buscar | Filtra por nome ou e-mail usando `busca` na URL. |
| Editar | Altera nome, e-mail, perfil, status e, se preenchida, a senha. |
| Ativar/desativar | Alterna `is_active`. |
| Excluir | Remove outro usuário; o administrador logado não pode apagar a própria conta. |

Quando o administrador muda o próprio nome ou perfil, os valores equivalentes da sessão são atualizados imediatamente.

## Banco de dados

### Tabelas usadas hoje pelo PHP

| Tabela | Quem usa | Finalidade |
| --- | --- | --- |
| `users_login` | `Auth.php`, `painel.php`, `crud.php` | Contas, senhas com hash, perfis e status de acesso. |
| `pedidos` | `pedidos.php`, `Pedidos.php` | Mesa, valor, status de pagamento e data de criação. É criada/atualizada automaticamente ao abrir a tela de pedidos. |

### Tabelas previstas em `mesa.sql`, mas ainda sem uso no PHP

| Tabela | Finalidade prevista |
| --- | --- |
| `categorias_produto` | Categorias específicas do cardápio. |
| `tipos_produto` | Tipos gerais de produto. |
| `produtos` | Itens do cardápio e preços. |
| `tables` | Mesas físicas e reservas. |
| `comandapedidos` | Pedidos ligados a mesas e produtos. |
| `login_audit` | Histórico de tentativas de login. |

Essas tabelas fazem parte do modelo do restaurante, mas a interface atual ainda não oferece telas nem consultas para elas. Também não há, no PHP atual, uso de `failed_attempts`, `locked_until` ou `login_audit`.

### Instalação do banco

1. Crie o banco `restaurante` no MySQL.
2. Importe `mesa.sql`.
3. Aplique a migração abaixo, pois o arquivo SQL cria as tabelas sem as chaves primárias e `AUTO_INCREMENT` necessários para os cadastros atuais:

```sql
ALTER TABLE `categorias_produto` ADD PRIMARY KEY (`id`);
ALTER TABLE `categorias_produto` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tipos_produto` ADD PRIMARY KEY (`id`);
ALTER TABLE `tipos_produto` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `produtos` ADD PRIMARY KEY (`id`);
ALTER TABLE `produtos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_login` ADD PRIMARY KEY (`id`);
ALTER TABLE `users_login` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
```

O usuário inicial definido no SQL é `admin@restaurante.local`. A senha descrita no arquivo é `admin`.

## Proteções já presentes

- PDO com consultas preparadas nas partes que recebem dados do usuário;
- `password_hash()` e `password_verify()` para senhas;
- validação de e-mail, ID e valores antes das gravações;
- `htmlspecialchars()` ao imprimir dados variáveis na tela;
- página de funcionários protegida para administradores;
- pastas com código interno bloqueadas pelo `.htaccess`.

## Pontos importantes para manutenção

- O painel permite que qualquer usuário autenticado altere o e-mail de qualquer conta listada. Isso é o comportamento atual de `painel.php`.
- A tela de pedidos usa a tabela simples `pedidos`; ela contém mesa, valor e pagamento. A classe `Pedidos` atualiza instalações antigas que possuíam essa tabela sem mesa e pagamento.
- Há dois modelos de pedido: `pedidos` (usado pela interface atual) e `comandapedidos` (previsto no banco, mas ainda não usado). Antes de criar uma cozinha/comanda detalhada, escolha e mantenha apenas um modelo.
- `templates/index.php` foi mantido apenas para redirecionar links antigos ao painel atual.
- As credenciais do MySQL estão em `config/conexao.php`. Em produção, use usuário próprio do banco e uma senha forte.

## Como executar localmente

1. Inicie Apache e MySQL no XAMPP.
2. Configure as credenciais de banco em `config/conexao.php` se necessário.
3. Crie o banco e importe `mesa.sql`, aplicando a migração desta documentação.
4. Abra `http://localhost/restauranteSteelTeam/templates/login.php`.

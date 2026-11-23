# Cloudflare Turnstile Integration

A lightweight WordPress plugin that adds [Cloudflare Turnstile](https://www.cloudflare.com/products/turnstile/) to the login and comment forms. No tracking, no ads, one file.

[Português abaixo](#português)

## Features
- Turnstile on the WordPress login form (`wp-login.php`).
- Turnstile on the comment form for visitors.
- Settings page under **Settings > Turnstile Settings**.

## Installation
1. Download this repository as a ZIP (or clone it) into `/wp-content/plugins/cloudflare-turnstile-integration/`.
2. Activate **Cloudflare Turnstile Integration** in **Plugins**.
3. In the Cloudflare dashboard, open **Turnstile**, add your site and copy the **Site Key** and **Secret Key**.
4. Paste both keys in **Settings > Turnstile Settings** and save.

Tip: before logging out, open `wp-login.php` in a private window and confirm the widget appears and login works.

## Requirements
WordPress 5.0+ and PHP 7.4+.

## License
MIT. See [LICENSE](LICENSE).

## Author
Guga Venancio

---

## Português

Plugin leve para WordPress que adiciona o [Cloudflare Turnstile](https://www.cloudflare.com/pt-br/products/turnstile/) aos formulários de login e de comentários. Sem rastreamento, sem anúncios, um arquivo só.

### Recursos
- Turnstile no formulário de login do WordPress (`wp-login.php`).
- Turnstile no formulário de comentários para visitantes.
- Página de configuração em **Configurações > Turnstile Settings**.

### Instalação
1. Baixe este repositório em ZIP (ou clone) para `/wp-content/plugins/cloudflare-turnstile-integration/`.
2. Ative **Cloudflare Turnstile Integration** em **Plugins**.
3. No painel da Cloudflare, abra **Turnstile**, adicione seu site e copie a **Site Key** e a **Secret Key**.
4. Cole as duas chaves em **Configurações > Turnstile Settings** e salve.

Dica: antes de sair da conta, abra `wp-login.php` numa janela anônima e confira se o widget aparece e o login funciona.

### Requisitos
WordPress 5.0+ e PHP 7.4+.

### Licença
MIT. Veja [LICENSE](LICENSE).

### Autor
Guga Venancio

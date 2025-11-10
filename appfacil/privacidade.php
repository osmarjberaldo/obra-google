<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - Gestão de Obra Fácil</title>
    <style>
        :root {
            --primary-color: #FF5B00;
            --secondary-color: #E65100;
            --accent-color: #FF8F00;
            --error-color: #D32F2F;
            --success-color: #388E3C;
            --warning-color: #F57C00;
            --text-color: #333333;
            --light-bg: #FFF3E0;
            --white: #FFFFFF;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }

        body {
            background-color: #f9f9f9;
            color: var(--text-color);
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 50px 0;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-bottom: 4px solid rgba(255,255,255,0.1);
        }

        h1 {
            font-size: 2.8rem;
            margin-bottom: 15px;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        h2 {
            color: var(--primary-color);
            margin: 40px 0 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--primary-color);
            font-weight: 600;
            font-size: 1.8rem;
        }

        h3 {
            color: var(--secondary-color);
            margin: 30px 0 18px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .content {
            background: white;
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 50px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .section {
            margin-bottom: 40px;
        }

        p {
            margin-bottom: 20px;
            color: #444;
            line-height: 1.8;
            font-size: 1.05rem;
        }

        ul, ol {
            margin: 15px 0 20px 30px;
        }
        
        ul li, ol li {
            margin-bottom: 10px;
            line-height: 1.7;
        }

        li {
            margin-bottom: 8px;
        }

        .highlight {
            background-color: rgba(255, 143, 0, 0.12);
            border-left: 4px solid var(--accent-color);
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            line-height: 1.7;
        }

        .note {
            background-color: rgba(255, 91, 0, 0.08);
            border-left: 4px solid var(--primary-color);
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            line-height: 1.7;
        }

        .warning {
            background-color: rgba(245, 124, 0, 0.12);
            border-left: 4px solid var(--warning-color);
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            line-height: 1.7;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }

        footer p {
            margin: 0;
            color: white;
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px 15px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
            color: var(--text-color);
        }
        
        body {
            background-color: var(--light-bg);
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
        }
        
        header h1 {
            text-align: center;
            margin: 0;
            font-size: 2.2em;
        }
        
        .content {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section:last-child {
            margin-bottom: 0;
        }
        
        h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            font-size: 1.8em;
        }
        
        h3 {
            color: var(--secondary-color);
            margin: 25px 0 15px;
            font-size: 1.4em;
        }
        
        h4 {
            color: var(--secondary-color);
            margin: 20px 0 10px;
            font-size: 1.2em;
        }
        
        p {
            margin-bottom: 15px;
            text-align: justify;
        }
        
        ul, ol {
            margin-bottom: 20px;
            padding-left: 30px;
        }
        
        li {
            margin-bottom: 8px;
        }
        
        .highlight {
            background-color: #f8f9fa;
            border-left: 4px solid var(--accent-color);
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
        }
        
        .highlight p:last-child {
            margin-bottom: 0;
        }
        
        .note {
            background-color: #e3f2fd;
            border-left: 4px solid var(--primary-color);
        }
        
        .warning {
            background-color: #fff8e1;
            border-left: 4px solid var(--warning-color);
        }
        
        .important {
            background-color: #ffebee;
            border-left: 4px solid var(--error-color);
        }
        
        .success {
            background-color: #e8f5e9;
            border-left: 4px solid var(--success-color);
        }
        
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            header h1 {
                font-size: 1.8em;
            }
            
            h2 {
                font-size: 1.5em;
            }
            
            h3 {
                font-size: 1.3em;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Política de Privacidade</h1>
            <p style="color: #ffffff;">Última atualização: <?php echo date('d/m/Y'); ?></p>
        </div>
    </header>
    
    <div class="container">
        <div class="content">
            <div class="section">
                <h2>1. INFORMAÇÕES GERAIS</h2>
                
                <p>A presente Política de Privacidade contém informações sobre coleta, uso, armazenamento, tratamento e proteção dos dados pessoais dos usuários e visitantes do App Gestão Fácil ou site https://gestaodeobrafacil.com, com a finalidade de demonstrar absoluta transparência quanto ao assunto e esclarecer a todos interessados sobre os tipos de dados que são coletados, os motivos da coleta e a forma como os usuários podem gerenciar ou excluir as suas informações pessoais.</p>
                
                <div class="note">
                    <p><strong>Importante:</strong> Esta Política de Privacidade foi elaborada em conformidade com a Lei Federal n. 12.965 de 23 de abril de 2014 (Marco Civil da Internet) e com a Lei Federal n. 13.709, de 14 de agosto de 2018 (Lei de Proteção de Dados Pessoais - LGPD).</p>
                </div>
                
                <p>Ao utilizar nossos serviços, você concorda com as práticas descritas nesta Política de Privacidade. Caso não concorde com esta Política, por favor, não utilize nossos serviços.</p>
                
                <p>Esta Política de Privacidade aplica-se a todos os usuários e visitantes do App Gestão Fácil ou site https://gestaodeobrafacil.com e integra os Termos e Condições Gerais de Uso,  doravante nominada Update Digital.</p>
                
                <p>A nossa Política de Privacidade esclarece como a Update Digital coleta e trata seus dados individuais:</p>
                
                <ul>
                    <li>Qualquer informação fornecida pelos usuários será coletada e guardada de acordo com os mais rígidos padrões de segurança e confiabilidade;</li>
                    <li>Todas as informações coletadas dos usuários trafegam de forma segura, utilizando processo de criptografia padrão da Internet;</li>
                    <li>As informações pessoais que nos forem fornecidas pelos usuários serão coletadas por meios éticos e legais;</li>
                    <li>As informações pessoais requeridas no processo de solicitação do cadastro são previstas em lei e/ou regulamentação específica. Outros dados poderão ser solicitados para identificação do usuário, para acesso ao aplicativo ou a conteúdo e/ou serviços restritos, ou ainda para melhorar os serviços prestados e a experiência dos usuários;</li>
                    <li>Será solicitada autorização prévia para a coleta automática de dados dos usuários, informando também sobre os usos desses dados, ficando a seu critério fornecê-la ou não;</li>
                    <li>A menos que haja uma determinação legal ou judicial, as informações dos usuários jamais serão transferidas a terceiros ou usadas para finalidades diferentes daquelas para as quais foram coletadas;</li>
                    <li>O acesso às informações coletadas está restrito a funcionários autorizados para o uso adequado desses dados;</li>
                    <li>Os funcionários que se utilizarem indevidamente dessas informações, ferindo nossa Política de Privacidade e demais políticas internas, estarão sujeitos às penalidades previstas em nosso processo disciplinar e em lei;</li>
                    <li>Manteremos a integridade das informações que nos forem fornecidas;</li>
                    <li>Nossos sites contêm links para outros sites externos cujos conteúdos e políticas de privacidade não são de responsabilidade da Update Digital;</li>
                    <li>A Update Digital poderá utilizar, formatar e divulgar depoimentos de usuários postados nas redes sociais, juntamente com seu nome e imagens (incluindo fotos de perfil), na página do App Gestão Fácil, aplicativo e/ou materiais institucionais e publicitários para a divulgação dos serviços prestados pela empresa com base na opinião de seus usuários;</li>
                    <li>Eventualmente, poderemos utilizar cookies (*) para confirmar sua identidade, personalizar seu acesso e acompanhar a utilização de nosso website visando o aprimoramento de sua navegação e funcionalidade;</li>
                    <li>A Update Digital coloca à disposição de seus usuários, canais de atendimento ao cliente (telefone: (43) 93300-2712, e-mail: suporte@gestaodeobrafacil.com e chat online: https://gestaodeobrafacil.com) para esclarecer qualquer dúvida.</li>
                </ul>
                
                <p>O App Gestão Fácil se compromete a cumprir as normas previstas na Lei Geral de Proteção de Dados (LGPD), e respeitar os princípios dispostos no Art. 6º:</p>
                
                <h3>Princípios do Tratamento de Dados</h3>
                <p>O tratamento de dados pessoais realizado pelo App Gestão Fácil observa os seguintes princípios:</p>
                
                <ul>
                    <li><strong>Finalidade:</strong> Realização do tratamento para propósitos legítimos, específicos, explícitos e informados ao titular, sem possibilidade de tratamento posterior de forma incompatível com essas finalidades.</li>
                    
                    <li><strong>Adequação:</strong> Compatibilidade do tratamento com as finalidades informadas ao titular, de acordo com o contexto do tratamento.</li>
                    
                    <li><strong>Necessidade:</strong> Limitação do tratamento ao mínimo necessário para a realização de suas finalidades, com abrangência dos dados pertinentes, proporcionais e não excessivos em relação às finalidades do tratamento de dados.</li>
                    
                    <li><strong>Livre acesso:</strong> Garantia, aos titulares, de consulta facilitada e gratuita sobre a forma e a duração do tratamento, bem como sobre a integralidade de seus dados pessoais.</li>
                    
                    <li><strong>Qualidade dos dados:</strong> Garantia, aos titulares, de exatidão, clareza, relevância e atualização dos dados, de acordo com a necessidade e para o cumprimento da finalidade de seu tratamento.</li>
                    
                    <li><strong>Transparência:</strong> Garantia, aos titulares, de informações claras, precisas e facilmente acessíveis sobre a realização do tratamento e os respectivos agentes de tratamento, observados os segredos comercial e industrial.</li>
                    
                    <li><strong>Segurança:</strong> Utilização de medidas técnicas e administrativas aptas a proteger os dados pessoais de acessos não autorizados e de situações acidentais ou ilícitas de destruição, perda, alteração, comunicação ou difusão.</li>
                    
                    <li><strong>Prevenção:</strong> Adoção de medidas para prevenir a ocorrência de danos em virtude do tratamento de dados pessoais.</li>
                    
                    <li><strong>Não discriminação:</strong> Impossibilidade de realização do tratamento para fins discriminatórios ilícitos ou abusivos.</li>
                    
                    <li><strong>Responsabilização e prestação de contas:</strong> Demonstração, pelo agente, da adoção de medidas eficazes e capazes de comprovar a observância e o cumprimento das normas de proteção de dados pessoais e, inclusive, da eficácia dessas medidas.</li>
                </ul>
                
                <h2>2. AGENTES DE TRATAMENTO</h2>
                
                <p>A quem compete as decisões referentes ao tratamento de dados pessoais realizado no serviço App Gestão Fácil (Controlador)?</p>
                
                <p>A Lei Geral de Proteção de Dados define como controlador, em seu artigo 5º:</p>
                
                <p>Art. 5º, VI – controlador: pessoa natural ou jurídica, de direito público ou privado, a quem competem as decisões referentes ao tratamento de dados pessoais;</p>
                
                <p>Para o serviço App Gestão Fácil, as decisões referentes ao tratamento de dados pessoais são de responsabilidade da Update Digital.</p>
                
                <p>Quem realiza o tratamento de dados (Operador)?</p>
                
                <p>A Lei Geral de Proteção de Dados define como operador, em seu artigo 5º:</p>
                
                <p>Art. 5º, VII – operador: pessoa natural ou jurídica, de direito público ou privado, que realiza o tratamento de dados pessoais em nome do controlador.</p>
                
                <p>Para o serviço App Gestão Fácil, o Controlador Update Digital também atua como operador, ou seja, além de ser responsável pelas decisões referentes ao tratamento de dados pessoais, também realiza o tratamento de dados pessoais.</p>
                
                <p>Quem é o responsável por atuar como canal de comunicação entre o controlador, os titulares dos dados e a Autoridade Nacional de Proteção de Dados (Encarregado)?</p>
                
                <p>A Lei Geral de Proteção de Dados define como encarregado, em seu artigo 5º:</p>
                
                <p>Art. 5º, VIII – pessoa indicada pelo controlador e operador para atuar como canal de comunicação entre o controlador, os titulares dos dados e a Autoridade Nacional de Proteção de Dados (ANPD).</p>
                
                <p>Para o serviço App Gestão Fácil, quem é responsável por atuar como canal de comunicação entre o controlador, os titulares dos dados e a Autoridade Nacional de Proteção de Dados é o encarregado Tairo Oliveira Lima.</p>
                
                <h2>3. COMO RECOLHEMOS OS DADOS PESSOAIS DO USUÁRIO E DO VISITANTE?</h2>
                
                <p>Os dados pessoais do usuário e visitante são recolhidos pela plataforma da seguinte forma:</p>
                
                <h3>Como Coletamos Seus Dados</h3>
                
                <div class="highlight">
                    <p>Coletamos seus dados das seguintes formas:</p>
                </div>
                
                <ul>
                    <li><strong>Criação de Conta/Perfil:</strong> Quando o usuário cria uma conta/perfil na plataforma App Gestão Fácil, coletamos dados de identificação básicos, como: nome completo, e-mail, empresa, telefone e cargo. Estes dados nos permitem identificar o usuário e garantir maior segurança aos seus acessos.</li>
                    
                    <li><strong>Navegação no Site/Aplicativo:</strong> Quando você acessa nossas páginas ou utiliza nosso aplicativo, coletamos automaticamente informações sobre sua interação, como páginas visitadas, termos de busca utilizados, documentos acessados, comentários realizados, URL de origem, tipo de navegador, endereço IP e dados de acesso.</li>
                    
                    <li><strong>Formulários Eletrônicos:</strong> Coletamos informações fornecidas por você através de nossos formulários de cadastro, contato, suporte e outros meios de interação disponíveis em nossa plataforma.</li>
                    
                    <li><strong>Dados Coletados:</strong> Nome completo do usuário, CPF, endereço, e-mail, número de telefone, foto do usuário, cargo, função, nome do responsável, nome do contratante, nome da contratada, nome do cliente, nome do funcionário e endereço IP.</li>
                </ul>
                
                <h2>4. PARA QUE FINALIDADES UTILIZAMOS OS DADOS PESSOAIS DO USUÁRIO E VISITANTE?</h2>
                
                <p>Os dados pessoais do usuário e do visitante coletados e armazenados pelo App Gestão Fácil tem por finalidade:</p>
                
                <ul>
                    <li>Bem-estar do usuário e visitante: aprimorar o produto e/ou serviço oferecido, facilitar, agilizar e cumprir os compromissos estabelecidos entre o usuário e a empresa, melhorar a experiência dos usuários e fornecer funcionalidades específicas a depender das características básicas do usuário.</li>
                    <li>Melhorias da plataforma: compreender como o usuário utiliza os serviços da plataforma, para ajudar no desenvolvimento de negócios e técnicas.</li>
                    <li>Comercial: os dados são usados para personalizar o conteúdo oferecido e gerar subsídio à plataforma para a melhora da qualidade no funcionamento dos serviços.</li>
                    <li>Previsão do perfil do usuário: tratamento automatizado de dados pessoais para avaliar o uso na plataforma.</li>
                    <li>Dados de cadastro: para permitir o acesso do usuário a determinados conteúdos da plataforma, exclusivo para usuários cadastrados.</li>
                    <li>Dados de contrato: conferir às partes segurança jurídica e facilitar a conclusão do negócio.</li>
                </ul>
                
                <p>O tratamento de dados pessoais para finalidades não previstas nesta Política de Privacidade somente ocorrerá mediante comunicação prévia ao usuário, de modo que os direitos e obrigações aqui previstos permanecem aplicáveis.</p>
                
                <h2>5. QUAL O TRATAMENTO REALIZADO COM OS DADOS PESSOAIS?</h2>
                
                <p>Coleta, processamento, produção, utilização e eliminação:</p>
                
                <ul>
                    <li>Nome completo do usuário e e-mail.</li>
                </ul>
                
                <p>Coleta, armazenamento, classificação, transferência, utilização e eliminação:</p>
                
                <ul>
                    <li>Foto do usuário, cargo, IP, cookies, CPF, endereço, nome do responsável, nome do contratante, nome da contratada, nome do cliente, nome do funcionário e função.</li>
                </ul>
                
                <p>Coleta, armazenamento, classificação, transferência, transmissão, utilização e eliminação:</p>
                
                <ul>
                    <li>Número de telefone.</li>
                </ul>
                
                <h2>6. POR QUANTO TEMPO OS DADOS PESSOAIS FICAM ARMAZENADOS?</h2>
                
                <p>Os dados pessoais do usuário e visitante são armazenados pela plataforma durante o período necessário para a prestação do serviço ou o cumprimento das finalidades previstas no presente documento, conforme o disposto no inciso I do artigo 15 da Lei 13.709/18.</p>
                
                <p>Os dados podem ser removidos ou anonimizados a pedido do usuário, excetuando os casos em que a lei oferecer outro tratamento.</p>
                
                <p>Ainda, os dados pessoais dos usuários apenas podem ser conservados após o término de seu tratamento nas seguintes hipóteses previstas no artigo 16 da referida lei:</p>
                
                <h3>Bases Legais para o Tratamento de Dados</h3>
                
                <p>O tratamento de dados pessoais somente será realizado nas seguintes hipóteses:</p>
                
                <ul>
                    <li><strong>Consentimento do titular:</strong> Quando você nos fornece seu consentimento livre, informado e inequívoco para o tratamento de seus dados pessoais para finalidades específicas.</li>
                    
                    <li><strong>Cumprimento de obrigação legal:</strong> Quando necessário para o cumprimento de obrigação legal ou regulatória pelo controlador.</li>
                    
                    <li><strong>Pesquisa acadêmica:</strong> Para realização de estudos por órgão de pesquisa, garantida, sempre que possível, a anonimização dos dados pessoais.</li>
                    
                    <li><strong>Transferência a terceiros:</strong> Quando houver necessidade de transferência a terceiro, desde que respeitados os requisitos de tratamento de dados dispostos na LGPD.</li>
                    
                    <li><strong>Uso exclusivo do controlador:</strong> Para uso exclusivo do controlador, vedado seu acesso por terceiro, e desde que anonimizados os dados.</li>
                </ul>
                
                <h2>7. SEGURANÇA DOS DADOS PESSOAIS ARMAZENADOS</h2>
                
                <p>O serviço App Gestão Fácil se compromete a aplicar as medidas técnicas e organizativas aptas a proteger os dados pessoais de acessos não autorizados e de situações de destruição, perda, alteração, comunicação ou difusão de tais dados.</p>
                
                <p>Para a garantia da segurança, serão adotadas soluções que levem em consideração: as técnicas adequadas; os custos de aplicação; a natureza, o âmbito, o contexto e as finalidades do tratamento; e os riscos para os direitos e liberdades do usuário.</p>
                
                <p>O serviço App Gestão Fácil utiliza criptografia em todas comunicações que realiza, de forma a fornecer confidencialidade dos dados pessoais e informações que trafegam entre o titular e o provedor, e evitar que acessos indevidos ocorram.</p>
                
                <p>O site utiliza criptografia para que os dados sejam transmitidos de forma segura e confidencial, de maneira que a transmissão dos dados entre o servidor e o usuário, e em retroalimentação, ocorra de maneira totalmente cifrada ou encriptada usando a tecnologia “secure socket layer” (SSL).</p>
                
                <p>No entanto, o site se exime de responsabilidade por culpa exclusiva de terceiro, como em caso de ataque de hackers ou crackers, ou culpa exclusiva do usuário, como no caso em que ele mesmo transfere seus dados a terceiro.</p>
                
                <p>O serviço App Gestão Fácil se compromete, ainda, a comunicar o usuário em prazo adequado caso ocorra algum tipo de violação da segurança de seus dados pessoais que possa lhe causar um alto risco para seus direitos e liberdades pessoais.</p>
                
                <p>A violação de dados pessoais é uma violação de segurança que provoque, de modo acidental ou ilícito, a destruição, a perda, a alteração, a divulgação ou o acesso não autorizado a dados pessoais transmitidos, conservados ou sujeitos a qualquer outro tipo de tratamento.</p>
                
                <p>Seus dados poderão ser armazenados e processados em servidores localizados fora do Brasil, inclusive nos Estados Unidos, onde podem não existir leis de proteção de dados equivalentes àquelas previstas na LGPD.</p>
                
                <p>Para garantir a segurança e conformidade com a legislação brasileira, adotamos as seguintes medidas:</p>
                
                <ul>
                    <li>Armazenamento em provedores de nuvem reconhecidos internacionalmente e com padrões elevados de segurança da informação, como [Microsoft Azure / AWS /  Firebase];</li>
                    <li>Adoção de medidas técnicas e administrativas para proteção dos dados.</li>
                </ul>
                
                <p>Por fim, o site compromete-se a tratar os dados pessoais do usuário com confidencialidade, dentro dos limites legais.</p>
                
                <h2>8. COMPARTILHAMENTO DOS DADOS</h2>
                
                <p>Os dados pessoais do usuário poderão ser compartilhados com as seguintes pessoas ou empresas: GERENCIANET S/A.</p>
                
                <p>Com relação aos fornecedores de serviços terceirizados como processadores de transação de pagamento, informamos que cada qual tem sua própria política de privacidade.</p>
                
                <p>Desse modo, recomendamos a leitura das suas políticas de privacidade para compreensão de quais informações pessoais serão usadas por esses fornecedores.</p>
                
                <p>Os fornecedores podem ser localizados ou possuir instalações localizadas em países diferentes.</p>
                
                <p>Nessas condições, os dados pessoais transferidos podem se sujeitar às leis de jurisdições nas quais o fornecedor de serviço ou suas instalações estão localizados.</p>
                
                <p>Ao acessar nossos serviços e prover suas informações, você está consentindo o processamento, transferência e armazenamento desta informação em outros países.</p>
                
                <div class="warning">
                    <p><strong>Atenção:</strong> Ao ser redirecionado para um aplicativo ou site de terceiros, você não será mais regido por esta Política de Privacidade ou pelos Termos de Serviço da nossa plataforma.</p>
                    <p>Não somos responsáveis pelas práticas de privacidade de outros sites e incentivamos você a ler atentamente as políticas de privacidade desses terceiros antes de fornecer quaisquer informações pessoais.</p>
                </div>
                
                <h2>9. COOKIES E TECNOLOGIAS SIMILARES</h2>
                
                <p>Utilizamos cookies e tecnologias semelhantes para melhorar sua experiência em nosso site e aplicativo. Os cookies são pequenos arquivos de texto que são armazenados no seu dispositivo quando você acessa nossos serviços.</p>
                
                <p>Cookies são pequenos arquivos de texto enviados pelo site ao computador do usuário e que nele ficam armazenados, com informações relacionadas à navegação do site.</p>
                
                <p>Por meio dos cookies, pequenas quantidades de informação são armazenadas pelo navegador do usuário para que nosso servidor possa lê-las posteriormente.</p>
                
                <p>Podem ser armazenados, por exemplo, dados sobre o dispositivo utilizado pelo usuário, bem como seu local e horário de acesso ao site.</p>
                
                <p>É importante ressaltar que nem todo cookie contém dados pessoais do usuário, já que determinados tipos de cookies podem ser utilizados somente para que o serviço funcione corretamente.</p>
                
                <p>As informações eventualmente armazenadas em cookies também são consideradas dados pessoais e todas as regras previstas nesta Política de Privacidade também são aplicáveis a eles.</p>
                
                <p>O serviço App Gestão Fácil utiliza cookies para que possamos oferecer o serviço, manter o usuário logado e validar as credenciais de acesso à plataforma.</p>
                
                <h2>10. CONSENTIMENTO</h2>
                
                <p>O usuário, ao acessar e utilizar o site, declara ter lido, compreendido e aceitado esta Política de Privacidade, bem como a coleta, armazenamento, tratamento e uso de seus dados pessoais, conforme aqui descrito.</p>
                
                <p>O usuário também declara estar ciente de seus direitos e de como exercê-los, conforme previsto na Lei Geral de Proteção de Dados Pessoais.</p>
                
                <p>Caso o usuário não esteja de acordo com esta Política de Privacidade, deverá se abster de utilizar o site e/ou serviços oferecidos pela plataforma.</p>
                
                <p>A Update Digital se reserva o direito de modificar esta Política de Privacidade a qualquer momento, sem aviso prévio, por isso recomendamos que o usuário a revise periodicamente.</p>
                
                <p>A versão mais recente da Política de Privacidade estará sempre disponível no site.</p>
                
                <p>Esta Política de Privacidade foi atualizada pela última vez em [data].</p>
                
                <p>Se você tiver alguma dúvida ou preocupação sobre esta Política de Privacidade, por favor, entre em contato conosco pelo e-mail [e-mail].</p>
                
                <p>Agradecemos sua atenção e cooperação.</p>
                
                <p>Atenciosamente,</p>                
                
                <p>Site: https://gestaodeobrafacil.com</p>
            </div>
            
            <div class="section">
                <h2>10. CONTATO E DÚVIDAS</h2>
                
                <p>Se você tiver alguma dúvida sobre esta Política de Privacidade ou sobre como tratamos seus dados pessoais, entre em contato conosco através dos seguintes canais:</p>
                
                <ul>
                    <li><strong>E-mail:</strong> privacidade@gestaodeobrafacil.com</li>
                    <li><strong>Endereço:</strong> [Endereço da Empresa]</li>
                    <li><strong>Telefone:</strong> [Telefone de Contato]</li>
                </ul>
                
                <div class="note">
                    <p><strong>Encarregado de Dados (DPO):</strong> [Nome do Encarregado]<br>
                    <strong>E-mail do DPO:</strong> dpo@gestaodeobrafacil.com</p>
                </div>
            </div>
            
            <div class="section">
                <h2>11. ALTERAÇÕES A ESTA POLÍTICA</h2>
                
                <p>Esta Política de Privacidade poderá ser atualizada periodicamente para refletir mudanças em nossas práticas de privacidade. Publicaremos qualquer alteração nesta página e, se as alterações forem significativas, forneceremos um aviso mais proeminente (incluindo, para certos serviços, notificação por e-mail das alterações nas políticas de privacidade).</p>
                
                <p>Recomendamos que você revise esta Política de Privacidade regularmente para se manter informado sobre como estamos protegendo suas informações.</p>
            </div>
        </div>

        <div class="note">
            <p>Os funcionários que se utilizarem indevidamente dessas informações, ferindo nossa Política de Privacidade e demais políticas internas, estarão sujeitos às penalidades previstas em nosso processo disciplinar e em lei.</p>
            <p>Manteremos a integridade das informações que nos forem fornecidas.</p>
            <p>Nossos sites contêm links para outros sites externos cujos conteúdos e políticas de privacidade não são de responsabilidade da Update Digital.</p>
            <p>A Update Digital poderá utilizar, formatar e divulgar depoimentos de usuários postados nas redes sociais, juntamente com seu nome e imagens (incluindo fotos de perfil), na página do App Gestão Fácil, aplicativo e/ou materiais institucionais e publicitários para a divulgação dos serviços prestados pela empresa com base na opinião de seus usuários.</p>
            <p>Eventualmente, poderemos utilizar cookies (*) para confirmar sua identidade, personalizar seu acesso e acompanhar a utilização de nosso website visando o aprimoramento de sua navegação e funcionalidade.</p>
            <p>A Update Digital coloca à disposição de seus usuários, canais de atendimento ao cliente (telefone: (43) 93300-2712, e-mail: suporte@gestaodeobrafacil.com e chat online: https://gestaodeobrafacil.com) para esclarecer qualquer dúvida.</p>
        </div>

        <p>O App Gestão Fácil a se compromete a cumprir as normas previstas na Lei Geral de Proteção de Dados (LGPD), e respeitar os princípios dispostos no Art. 6º:</p>

        <ul>
            <li><strong>Finalidade:</strong> Realização do tratamento para propósitos legítimos, específicos, explícitos e informados ao titular, sem possibilidade de tratamento posterior de forma incompatível com essas finalidades.</li>
            <li><strong>Adequação:</strong> Compatibilidade do tratamento com as finalidades informadas ao titular, de acordo com o contexto do tratamento.</li>
            <li><strong>Necessidade:</strong> Limitação do tratamento ao mínimo necessário para a realização de suas finalidades, com abrangência dos dados pertinentes, proporcionais e não excessivos em relação às finalidades do tratamento de dados.</li>
            <li><strong>Livre acesso:</strong> Garantia, aos titulares, de consulta facilitada e gratuita sobre a forma e a duração do tratamento, bem como sobre a integralidade de seus dados pessoais.</li>
            <li><strong>Qualidade dos dados:</strong> Garantia, aos titulares, de exatidão, clareza, relevância e atualização dos dados, de acordo com a necessidade e para o cumprimento da finalidade de seu tratamento.</li>
            <li><strong>Transparência:</strong> Garantia, aos titulares, de informações claras, precisas e facilmente acessíveis sobre a realização do tratamento e os respectivos agentes de tratamento, observados os segredos comercial e industrial.</li>
            <li><strong>Segurança:</strong> Utilização de medidas técnicas e administrativas aptas a proteger os dados pessoais de acessos não autorizados e de situações acidentais ou ilícitas de destruição, perda, alteração, comunicação ou difusão.</li>
            <li><strong>Prevenção:</strong> Adoção de medidas para prevenir a ocorrência de danos em virtude do tratamento de dados pessoais.</li>
            <li><strong>Não discriminação:</strong> Impossibilidade de realização do tratamento para fins discriminatórios ilícitos ou abusivos.</li>
            <li><strong>Responsabilização e prestação de contas:</strong> Demonstração, pelo agente, da adoção de medidas eficazes e capazes de comprovar a observância e o cumprimento das normas de proteção de dados pessoais e, inclusive, da eficácia dessas medidas.</li>
        </ul> 
</div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> GESTÃO DE OBRAS FACIL LTDA. Todos os direitos reservados.</p>
            <p>Versão da Política de Privacidade: 1.0 - Data de vigência: 01/09/2025</p>
        </div>
    </footer>
</body>
</html>

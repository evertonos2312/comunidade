<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Softonic\GraphQL\ClientBuilder;

class ContmakerService
{
    public function getMembros()
    {
        return [
            0 => ['email' => 'everton.oliveirasilva@outlook.com', 'nome' => 'Everton Silva']
        ];
    }

    public function getMensagem()
    {
        return "Hey, Contmaker! O que você achou da Comunidade Contábil Brasil?
         Incrível, né?! 🤩 Nós estávamos super ansiosos para te contar essa novidade.
          E, aliás, nós já temos mais uma! Esse e-mail é o convite para que você participe dela a partir de agora.
           Para isso, basta clicar no link abaixo e criar o seu perfil.
            A Comunidade Contábil Brasil é um espaço onde teremos a presença de nossos clientes e potenciais clientes para estreitarmos a comunicação.
             Para entender melhor o propósito dela, nós sugerimos que, assim que criar o seu perfil, acesse o nosso Manifesto.
              Vamos juntos!";
    }
}

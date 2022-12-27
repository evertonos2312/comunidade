<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Softonic\GraphQL\ClientBuilder;

class ContmakerService
{
    public function getMembros()
    {
        $csvFileName = "colaboradores_ativos.csv";
        $csvFile = public_path('csv/' . $csvFileName);

        ini_set('auto_detect_line_endings',TRUE);
        $row = 1;
        $membros = [];
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $data = array_map("utf8_encode", $data);
                if($data[0] != "" && $data[1] != "" && $data[0] != "Usuario" && $data[1] != "Email") {
                    $num = count($data);
                    $row++;
                    $membros[$row]['nome'] = $this->encodeToUtf8($data[0]);
                    $membros[$row]['email'] = $data[1];
                }
            }
            fclose($handle);
        }
        return $membros;
    }

    private function encodeToUtf8($string)
    {
        $encoding = mb_detect_encoding($string, 'UTF-8, ISO-8859-1, WINDOWS-1252, WINDOWS-1251', true);
        if ($encoding != 'UTF-8') {
            $string = iconv($encoding, 'UTF-8//IGNORE', $string);
        }
        return mb_convert_encoding( $string, 'Windows-1252', 'UTF-8');
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

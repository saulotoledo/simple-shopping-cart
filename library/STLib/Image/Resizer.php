<?php
/**
 * LICENSE
 *
 * This source file is subject to the BSD 3-Clause license.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to saulotoledo@gmail.com so we can send you a copy immediately.
 *
 * @category   STLib
 * @package    STLib_Image
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Trabalha o redimensionamento de imagens.
 *
 * @category   STLib
 * @package    STLib_Image
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class STLib_Image_Resizer
{
    /**
     * A imagem redimensionada.
     * @var resource
     */
    protected $image;

    /**
     * Informações sobre a atual imagem.
     * @var array
     */
    protected $imageType;

    /**
     * Construtor.
     *
     * @param string $filepath O caminho do arquivo.
     */
    public function __construct($filepath)
    {
        $imageInfo = getimagesize($filepath);
        $this->imageType = $imageInfo[2];

        if ($this->getImageType() == IMAGETYPE_JPEG) {
            $this->image = imagecreatefromjpeg($filepath);
        } elseif ($this->getImageType() == IMAGETYPE_GIF) {
            $this->image = imagecreatefromgif($filepath);
        } elseif ($this->getImageType() == IMAGETYPE_PNG) {
            $this->image = imagecreatefrompng($filepath);
        }
    }

    /**
     * Retorna o tipo da imagem de acordo com constantes
     * predefinidas do PHP.
     *
     * @return int O tipo da imagem.
     * @see http://www.php.net/manual/en/image.constants.php
     */
    public function getImageType()
    {
        return $this->imageType;
    }

    /**
     * Retorna a largura em pixels da imagem atual.
     *
     * @return int A largura em pixels da imagem atual.
     */
    public function getWidth()
    {
        return imagesx($this->image);
    }

    /**
     * Retorna a altura em pixels da imagem atual.
     *
     * @return int A altura em pixels da imagem atual.
     */
    public function getHeight()
    {
        return imagesy($this->image);
    }

    /**
     * Processa a imagem.
     *
     * @param int $imageType O tipo da imagem de acordo com constantes
     *        predefinidas do PHP.
     * @param int $quality A qualidade da imagem de 0 a 100. Se menor
     *        que zero, assume valor zero. Se maior que 100, assume
     *        valor 100. Se for um número real, é truncado para inteiro.
     * @param string $savePath O caminho da imagem a salvar. Se NULL,
     *        define um cabeçalho HTTP e envia a imagem para o browser.
     */
    private function processImage($imageType, $quality, $savePath = null)
    {
        // Trunca para inteiro:
        $quality = (int) $quality;

        // Corrige erros no valor informado:
        if ($quality < 0) {
            $quality = 0;
        } elseif ($quality > 100) {
            $quality = 100;
        }

        // Processa a imagem:
        if ($imageType == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $savePath, $quality);
        } elseif ($imageType == IMAGETYPE_GIF) {
            imagegif($this->image, $savePath);
        } elseif ($imageType == IMAGETYPE_PNG) {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
            imagepng($this->image, $savePath);
        }
    }

    /**
     * Envia a imagem para o browser.
     *
     * @param int $imageType O tipo da imagem de acordo com constantes
     *        predefinidas do PHP.
     * @param int $quality A qualidade da imagem de 0 a 100. Se menor
     *        que zero, assume valor zero. Se maior que 100, assume
     *        valor 100. Se for um número real, é truncado para inteiro.
     */
    public function output($imageType = IMAGETYPE_PNG, $quality = 85)
    {
        $this->processImage($imageType, $quality);
    }

    /**
     * Processa a imagem
     * @param int $imageType O tipo da imagem de acordo com constantes
     *        predefinidas do PHP.
     * @param int $quality A qualidade da imagem de 0 a 100. Se menor
     *        que zero, assume valor zero. Se maior que 100, assume
     *        valor 100. Se for um número real, é truncado para inteiro.
     * @param string $savePath O caminho da imagem a salvar. Se NULL,
     *        define um cabeçalho HTTP e envia a imagem para o browser.
     */
    public function saveAs($path, $imageType = IMAGETYPE_PNG, $quality = 85)
    {
        $this->processImage($imageType, $quality, $path);
        return $this;
    }

    /**
     * Redimensiona a imagem para a altura indicada.
     *
     * @param int $height A altura da imagem de destino.
     * @return STLib_Image_Resizer O próprio objeto.
     */
    public function resizeToHeight($height)
    {
        $prop = $height / $this->getHeight();
        $width = $this->getWidth() * $prop;
        $this->resizeTo($width, $height);

        return $this;
    }

    /**
     * Redimensiona a imagem para a largura indicada.
     *
     * @param int $width A largura da imagem de destino.
     * @return STLib_Image_Resizer O próprio objeto.
     */
    public function resizeToWidth($width)
    {
        $prop = $width / $this->getWidth();
        $height = $this->getheight() * $prop;
        $this->resizeTo($width, $height);

        return $this;
    }

    /**
     * Redimensiona a imagem proporcionalmente para largura
     * e altura indicadas. Se houver alguma sobra após
     * redução proporcional ela será cortada.
     *
     * @param int $width A largura da imagem de destino.
     * @param int $height A altura da imagem de destino.
     * @return STLib_Image_Resizer O próprio objeto.
     */
    public function resizeTo($width, $height)
    {
        $finalWidth  = $width;
        $finalHeight = $height;
        if ($this->getWidth()/$this->getHeight() >= $width/$height) {
            $finalWidth = $this->getWidth() / ($this->getHeight() / $height);
        } else {
            $finalHeight = $this->getHeight() / ($this->getWidth() / $width);
        }

        $finalImage = imagecreatetruecolor($width, $height);

        if ($this->getImageType() == IMAGETYPE_GIF || $this->getImageType() == IMAGETYPE_PNG) {
            imagealphablending($finalImage, false);
            imagesavealpha($finalImage, true);
            $transparent = imagecolorallocatealpha($finalImage, 255, 255, 255, 127);
            imagefilledrectangle($finalImage, 0, 0, $width, $height, $transparent);
        }

        imagecopyresampled(
            $finalImage,
            $this->image,
            0 - 0.5 * ($finalWidth - $width),
            0 - 0.5 * ($finalHeight - $height),
            0,
            0,
            $finalWidth,
            $finalHeight,
            $this->getWidth(),
            $this->getHeight()
        );

        $this->image = $finalImage;

        return $this;
    }
}

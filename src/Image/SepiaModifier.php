<?php

namespace Elegant\Media\Image;

use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\ModifierInterface;

class SepiaModifier implements ModifierInterface
{
    /**
     * Applies sepia filter to the image.
     *
     * @see https://github.com/thephpleague/glide/blob/master/src/Manipulators/Filter.php#L47-L52
     * @param ImageInterface $image
     * @return ImageInterface
     */
    public function apply(ImageInterface $image): ImageInterface
    {
        $image->greyscale();
        $image->brightness(-10);
        $image->contrast(10);
        $image->colorize(38, 27, 12);
        $image->brightness(-10);
        $image->contrast(10);

        return $image;
    }
}

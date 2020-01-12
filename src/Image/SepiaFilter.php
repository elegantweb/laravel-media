<?php

namespace Elegant\Media\Image;

use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class SepiaFilter implements FilterInterface
{
    /**
     * Applies sepia filter to the image.
     *
     * @see https://github.com/thephpleague/glide/blob/master/src/Manipulators/Filter.php#L47-L52
     * @param Image $image
     * @return Image
     */
    public function applyFilter(Image $image): Image
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

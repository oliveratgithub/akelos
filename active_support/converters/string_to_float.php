<?php

# This file is part of the Akelos Framework
# (Copyright) 2004-2010 Bermi Ferrer bermi a t bermilabs com
# See LICENSE and CREDITS for details

class AkStringToFloat
{
    public function convert() {
        return floatval($this->source);
    }
}


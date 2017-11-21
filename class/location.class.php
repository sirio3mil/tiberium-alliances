<?php
/*
Descripci�n: El algoritmo del punto en un pol�gono permite comprobar mediante
programaci�n si un punto est� dentro de un pol�gono o fuera de ello.
Autor: Micha�l Niessen (2009)
Sito web: AssemblySys.com
 
Si este c�digo le es �til, puede mostrar su
agradecimiento a Micha�l ofreci�ndole un caf� ;)
PayPal: michael.niessen@assemblysys.com
 
Mientras estos comentarios (incluyendo nombre y detalles del autor) est�n
incluidos y SIN ALTERAR, este c�digo est� distribuido bajo la GNU Licencia
P�blica General versi�n 3: http://www.gnu.org/licenses/gpl.html
*/
 
class Location {
    public $pointOnVertex = true; // Checar si el punto se encuentra exactamente en uno de los v�rtices?
 
	function pointInPolygon($point, $polygon, $pointOnVertex = true) {
        $this->pointOnVertex = $pointOnVertex;
 
        // Transformar la cadena de coordenadas en matrices con valores "x" e "y"
        $point = $this->pointStringToCoordinates($point);
        $vertices = array(); 
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex); 
        }
 
        // Checar si el punto se encuentra exactamente en un v�rtice
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }
 
        // Checar si el punto est� adentro del poligono o en el borde
        $intersections = 0; 
        $vertices_count = count($vertices);
 
        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1]; 
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Checar si el punto est� en un segmento horizontal
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) { 
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x']; 
                if ($xinters == $point['x']) { // Checar si el punto est� en un segmento (otro que horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++; 
                }
            } 
        } 
        // Si el n�mero de intersecciones es impar, el punto est� dentro del poligono. 
        if ($intersections % 2 != 0) {
            return "inside";
        } 
        else {
            return "outside";
        }
    }
 
    function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
 
    }
 
    function pointStringToCoordinates($pointString) {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }
}
?>
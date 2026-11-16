<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator;

use muqsit\vanillagenerator\generator\noise\glowstone\PerlinOctaveGenerator;
use RuntimeException;
use function array_slice;
use function array_values;
use function count;
use function fclose;
use function fread;
use function fwrite;
use function is_resource;
use function pack;
use function stream_set_blocking;
use function stream_set_timeout;
use function stream_set_write_buffer;
use function stream_socket_client;
use function strlen;
use function substr;
use function unpack;

final class NoiseAccelerator{

	public static ?string $endpoint = null;

	private static function encodeGroup(PerlinOctaveGenerator $group) : string{
		$octaves = $group->octaves;
		$payload = pack("v3e3C", $group->size_x, $group->size_y, $group->size_z, $group->x_scale, $group->y_scale, $group->z_scale, count($octaves));
		foreach($octaves as $octave){
			$payload .= pack("e3", $octave->offset_x, $octave->offset_y, $octave->offset_z);
			$payload .= pack("C*", ...$octave->perm);
		}
		return $payload;
	}

	/** @var resource|null */
	private $socket;
	private int $sample_count;

	public function __construct(PerlinOctaveGenerator $roughness, PerlinOctaveGenerator $roughness2, PerlinOctaveGenerator $detail){
		$groups = [$roughness, $roughness2, $detail];
		$this->sample_count = $roughness->size_x * $roughness->size_y * $roughness->size_z;

		$socket = @stream_socket_client(self::$endpoint, $errno, $error, 2.0);
		$socket !== false || throw new RuntimeException("Noise connection failed: {$error} ({$errno})");
		$this->socket = $socket;
		stream_set_timeout($socket, 5);
		stream_set_blocking($socket, true);
		stream_set_write_buffer($socket, 0);

		$payload = "";
		foreach($groups as $group){
			$payload .= self::encodeGroup($group);
		}
		$this->send(pack("V", strlen($payload)) . $payload);
	}

	/** @return array{list<float>, list<float>, list<float>} */
	public function sample(float $x, float $z) : array{
		$this->send(pack("e2", $x, $z));
		$values = array_values(unpack("e*", $this->readExact(3 * $this->sample_count * 8)));
		$n = $this->sample_count;
		return [array_slice($values, 0, $n), array_slice($values, $n, $n), array_slice($values, 2 * $n, $n)];
	}

	private function send(string $payload) : void{
		$size = strlen($payload);
		for($offset = 0; $offset < $size; $offset += $written){
			$written = @fwrite($this->socket, substr($payload, $offset));
			$written !== false || throw new RuntimeException("Noise socket write failed (false)");
			$written > 0 || throw new RuntimeException("Noise socket write failed (w == 0)");
		}
	}

	private function readExact(int $size) : string{
		$data = "";
		while(strlen($data) < $size){
			$part = @fread($this->socket, $size - strlen($data));
			$part !== false || throw new RuntimeException("Noise socket disconnected or timed out (false)");
			$part !== "" || throw new RuntimeException("Noise socket disconnected or timed out (empty)");
			$data .= $part;
		}
		return $data;
	}

	public function __destruct(){
		if(is_resource($this->socket)){
			fclose($this->socket);
		}
		$this->socket = null;
	}
}
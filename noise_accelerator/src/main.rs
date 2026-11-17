const PROTOCOL_VERSION: u32 = 10002;

use std::io::{self, Read, Write};
use std::net::TcpListener;
use std::path::PathBuf;
use std::thread;
use std::time::Duration;

struct YSample {
    iy: usize,
    dy: f64,
    fy: f64,
    changed: bool,
}

struct Octave {
    ox: f64,
    oz: f64,
    perm: [u8; 512],
    y: Vec<YSample>,
}

struct Group {
    sx: usize,
    sy: usize,
    sz: usize,
    scale_x: f64,
    scale_z: f64,
    octaves: Vec<Octave>,
}

fn take<'a>(input: &mut &'a [u8], len: usize) -> &'a [u8] {
    let (head, tail) = (*input).split_at(len);
    *input = tail;
    head
}

fn read_f64(input: &mut &[u8]) -> f64 {
    f64::from_le_bytes(take(input, 8).try_into().unwrap())
}

impl Group {
    fn read(input: &mut &[u8]) -> Self {
        let sx = u16::from_le_bytes(take(input, 2).try_into().unwrap()) as usize;
        let sy = u16::from_le_bytes(take(input, 2).try_into().unwrap()) as usize;
        let sz = u16::from_le_bytes(take(input, 2).try_into().unwrap()) as usize;
        let scale_x = read_f64(input);
        let scale_y = read_f64(input);
        let scale_z = read_f64(input);
        let count = take(input, 1)[0] as usize;
        let mut octaves = Vec::with_capacity(count);
        let mut frequency = 1.0;
        for _ in 0..count {
            let ox = read_f64(input);
            let oy = read_f64(input);
            let oz = read_f64(input);
            let perm = take(input, 512).try_into().unwrap();
            let step_y = scale_y * frequency;
            let mut previous_y = -1;
            let y = (0..sy).map(|k| {
                let mut dy = oy + k as f64 * step_y;
                let floor = dy.floor() as i64;
                let iy = (floor & 255) as usize;
                dy -= floor as f64;
                let changed = k == 0 || previous_y != iy as i32;
                previous_y = iy as i32;
                YSample { iy, dy, fy: fade(dy), changed }
            }).collect();
            octaves.push(Octave { ox, oz, perm, y });
            frequency *= 0.5;
        }
        Self { sx, sy, sz, scale_x, scale_z, octaves }
    }

    fn sample(&self, x: f64, z: f64, noise: &mut [f64]) {
        noise.fill(0.0);
        let x = x * self.scale_x;
        let z = z * self.scale_z;
        let mut frequency = 1.0;
        let mut amplitude = 1.0;

        for octave in &self.octaves {
            octave.add_noise(noise, self, wrap(x * frequency), wrap(z * frequency), frequency, amplitude);
            frequency *= 0.5;
            amplitude *= 2.0;
        }
    }
}

fn wrap(mut x: f64) -> f64 {
    let integer = if x >= 0.0 { x as i64 } else { x as i64 - 1 };
    x -= integer as f64;
    x += (integer % 16_777_216) as f64;
    x
}

fn fade(x: f64) -> f64 {
    x * x * x * (x * (x * 6.0 - 15.0) + 10.0)
}

fn lerp(t: f64, a: f64, b: f64) -> f64 {
    a + t * (b - a)
}

fn grad(hash: u8, x: f64, y: f64, z: f64) -> f64 {
    let h = hash & 15;
    let u = if h < 8 { x } else { y };
    let v = if h < 4 { y } else if h == 12 || h == 14 { x } else { z };
    (if h & 1 == 0 { u } else { -u }) + (if h & 2 == 0 { v } else { -v })
}

impl Octave {
    fn add_noise(&self, noise: &mut [f64], group: &Group, x: f64, z: f64, frequency: f64, amplitude: f64) {
        let p = &self.perm;
        let scale_x = group.scale_x * frequency;
        let scale_z = group.scale_z * frequency;
        let mut index = 0;

        for i in 0..group.sx {
            let mut dx = x + self.ox + i as f64 * scale_x;
            let ix_floor = dx.floor() as i64;
            let ix = (ix_floor & 255) as usize;
            dx -= ix_floor as f64;
            let fx = fade(dx);

            for j in 0..group.sz {
                let mut dz = z + self.oz + j as f64 * scale_z;
                let iz_floor = dz.floor() as i64;
                let iz = (iz_floor & 255) as usize;
                dz -= iz_floor as f64;
                let fz = fade(dz);
                let (mut x1, mut x2, mut x3, mut x4) = (0.0, 0.0, 0.0, 0.0);

                for y in &self.y {
                    if y.changed {
                        let a = p[ix] as usize + y.iy;
                        let aa = p[a] as usize + iz;
                        let ab = p[a + 1] as usize + iz;
                        let b = p[ix + 1] as usize + y.iy;
                        let ba = p[b] as usize + iz;
                        let bb = p[b + 1] as usize + iz;

                        x1 = lerp(fx, grad(p[aa], dx, y.dy, dz), grad(p[ba], dx - 1.0, y.dy, dz));
                        x2 = lerp(fx, grad(p[ab], dx, y.dy - 1.0, dz), grad(p[bb], dx - 1.0, y.dy - 1.0, dz));
                        x3 = lerp(fx, grad(p[aa + 1], dx, y.dy, dz - 1.0), grad(p[ba + 1], dx - 1.0, y.dy, dz - 1.0));
                        x4 = lerp(fx, grad(p[ab + 1], dx, y.dy - 1.0, dz - 1.0), grad(p[bb + 1], dx - 1.0, y.dy - 1.0, dz - 1.0));
                    }
                    noise[index] += lerp(fz, lerp(y.fy, x1, x2), lerp(y.fy, x3, x4)) * amplitude;
                    index += 1;
                }
            }
        }
    }
}

fn handle(mut socket: impl Read + Write) -> io::Result<()> {
    let mut length = [0; 4];
    socket.read_exact(&mut length)?;
    let mut init = vec![0; u32::from_le_bytes(length) as usize];
    socket.read_exact(&mut init)?;
    let mut input = init.as_slice();
    let groups = [Group::read(&mut input), Group::read(&mut input), Group::read(&mut input)];

    let mut values = vec![0.0f64; groups.iter().map(|g| g.sx * g.sy * g.sz).sum()];
    let mut response = vec![0u8; values.len() * 8];
    let mut opcode = [0; 1];
    let mut request = [0; 16];
    let mut random_request = [0; 36];
    let mut random_response = Vec::new();

    loop {
        match socket.read_exact(&mut opcode) {
            Ok(()) => {},
            Err(error) if error.kind() == io::ErrorKind::UnexpectedEof => break,
            Err(error) => return Err(error),
        }
        match opcode[0] {
            0 => {
                socket.read_exact(&mut request)?;
                let x = f64::from_le_bytes(request[..8].try_into().unwrap());
                let z = f64::from_le_bytes(request[8..].try_into().unwrap());

                let mut remaining = values.as_mut_slice();
                for group in &groups {
                    let (samples, rest) = remaining.split_at_mut(group.sx * group.sy * group.sz);
                    group.sample(x, z, samples);
                    remaining = rest;
                }

                for (bytes, value) in response.chunks_exact_mut(8).zip(&values) {
                    bytes.copy_from_slice(&value.to_le_bytes());
                }
                socket.write_all(&response)?;
            }
            1 => {
                socket.read_exact(&mut random_request)?;
                let mut x = i64::from_le_bytes(random_request[..8].try_into().unwrap());
                let mut y = i64::from_le_bytes(random_request[8..16].try_into().unwrap());
                let mut z = i64::from_le_bytes(random_request[16..24].try_into().unwrap());
                let mut w = i64::from_le_bytes(random_request[24..32].try_into().unwrap());
                let count = u32::from_le_bytes(random_request[32..].try_into().unwrap()) as usize;
                if count > 131_328 {
                    return Err(io::Error::new(io::ErrorKind::InvalidData, "RNG batch too large"));
                }
                random_response.resize(count * 4, 0);
                for bytes in random_response.chunks_exact_mut(4) {
                    let t = ((x as u64) ^ ((x as u64) << 11)) & 0xffffffff;
                    x = y;
                    y = z;
                    z = w;
                    w = ((w as u64 ^ (((w >> 19) as u64) & 0x7fffffff) ^ (t ^ ((t >> 8) & 0x7fffffff))) & 0xffffffff) as i64;
                    bytes.copy_from_slice(&(w as u32).to_le_bytes());
                }
                socket.write_all(&random_response)?;
            }
            _ => return Err(io::Error::new(io::ErrorKind::InvalidData, "Unknown command")),
        }
    }
    Ok(())
}

fn spawn(socket: impl Read + Write + Send + 'static) {
    thread::spawn(move || {
        if let Err(error) = handle(socket) {
            eprintln!("noise connection: {error}");
        }
    });
}

/// exit (and remove socket file) when parent closes stdin
fn watch_parent(socket_path: Option<PathBuf>) {
    thread::spawn(move || {
        let _ = io::copy(&mut io::stdin(), &mut io::sink());
        if let Some(path) = socket_path {
            let _ = std::fs::remove_file(path);
        }
        std::process::exit(0);
    });
}

fn announce(endpoint: &str) -> io::Result<()> {
    let mut out = io::stdout().lock();
    out.write_all(&PROTOCOL_VERSION.to_le_bytes())?;
    out.write_all(&(endpoint.len() as u16).to_le_bytes())?;
    out.write_all(endpoint.as_bytes())?;
    out.flush()
}

fn main() -> io::Result<()> {
    #[cfg(unix)]
    {
        use std::os::unix::net::UnixListener;

        let path = std::env::temp_dir().join(format!("vg-noise-accel-{}.sock", std::process::id()));
        if let Ok(listener) = UnixListener::bind(&path) {
			announce(&format!("unix://{}", path.display()))?;
            watch_parent(Some(path));
            for connection in listener.incoming() {
                match connection {
                    Ok(socket) => spawn(socket),
                    Err(error) => {
                        eprintln!("accept failed: {error}");
                        thread::sleep(Duration::from_millis(50)); // avoid a hot loop on EMFILE
                    }
                }
            }
            return Ok(());
        }
    }

    let listener = TcpListener::bind("127.0.0.1:0")?;
	announce(&format!("tcp://{}", listener.local_addr()?))?;
    io::stdout().flush()?;
    for connection in listener.incoming() {
        let socket = connection?;
        socket.set_nodelay(true)?;
        spawn(socket);
    }
    Ok(())
}
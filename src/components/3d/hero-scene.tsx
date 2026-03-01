'use client'

import { useRef } from 'react'
import { Canvas, useFrame } from '@react-three/fiber'
import { Float, Sparkles, Torus, MeshTransmissionMaterial } from '@react-three/drei'
import * as THREE from 'three'
import { useTheme } from '@/components/theme/theme-provider'

function Diamond({ isDark }: { isDark: boolean }) {
    const groupRef = useRef<THREE.Group>(null)

    useFrame((state) => {
        if (groupRef.current) {
            groupRef.current.rotation.y = state.clock.elapsedTime * 0.15
            groupRef.current.rotation.z = Math.sin(state.clock.elapsedTime * 0.5) * 0.1
        }
    })

    const color = '#FFFFFF' // Pure White core
    const accentColor = '#38bdf8' // Electric Cyan

    return (
        <group ref={groupRef} position={[3.2, 0, 0]}>
            <Float speed={2} rotationIntensity={0.5} floatIntensity={1}>
                {/* Core octahedron — White glass with blue transmission */}
                <mesh>
                    <octahedronGeometry args={[1.0, 0]} />
                    <MeshTransmissionMaterial
                        backside
                        samples={8}
                        resolution={256}
                        transmission={0.95}
                        roughness={0.02}
                        thickness={1.5}
                        ior={1.5}
                        chromaticAberration={0.06}
                        anisotropy={0.5}
                        distortion={0.1}
                        color={color}
                        emissive={accentColor}
                        emissiveIntensity={0.2}
                    />
                </mesh>

                {/* Pulse orbit ring — White */}
                <Torus args={[1.9, 0.005, 16, 100]} rotation={[Math.PI / 2, 0, 0]}>
                    <meshStandardMaterial
                        color="#FFFFFF"
                        emissive="#FFFFFF"
                        emissiveIntensity={2}
                        transparent
                        opacity={0.9}
                    />
                </Torus>

                {/* Faint orbit ring — Cyan */}
                <Torus args={[2.6, 0.002, 16, 100]} rotation={[-Math.PI / 4, Math.PI / 3, 0]}>
                    <meshStandardMaterial
                        color={accentColor}
                        emissive={accentColor}
                        emissiveIntensity={3}
                        transparent
                        opacity={0.4}
                    />
                </Torus>
            </Float>
        </group>
    )
}

export function Hero3DScene() {
    const { theme } = useTheme()
    const isDark = theme === 'dark'

    return (
        <div className="absolute inset-0 w-full h-full z-0 pointer-events-none">
            <Canvas
                camera={{ position: [0, 0, 8], fov: 40 }}
                gl={{ antialias: true, alpha: true }}
                dpr={[1, 2]}
                style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%' }}
            >
                <ambientLight intensity={isDark ? 0.4 : 0.8} />
                <spotLight position={[10, 10, 10]} angle={0.25} penumbra={1} intensity={2} color="#38bdf8" />
                <pointLight position={[-10, -5, 5]} intensity={1} color="#0c4a6e" />

                <Diamond isDark={isDark} />
            </Canvas>
        </div>
    )
}

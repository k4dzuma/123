'use client'

import { useRef } from 'react'
import { useFrame } from '@react-three/fiber'
import { Float, MeshDistortMaterial, MeshWobbleMaterial } from '@react-three/drei'
import * as THREE from 'three'

export function AncientCoin() {
    const meshRef = useRef<THREE.Mesh>(null)

    useFrame((state) => {
        if (!meshRef.current) return
        // Slow rotation
        meshRef.current.rotation.y += 0.005

        // Gentle tilt based on mouse position
        const { x, y } = state.mouse
        meshRef.current.rotation.x = THREE.MathUtils.lerp(meshRef.current.rotation.x, -y * 0.2, 0.1)
        meshRef.current.rotation.z = THREE.MathUtils.lerp(meshRef.current.rotation.z, x * 0.2, 0.1)
    })

    return (
        <Float
            speed={2}
            rotationIntensity={0.5}
            floatIntensity={1}
        >
            <mesh ref={meshRef} castShadow receiveShadow rotation={[Math.PI / 2, 0, 0]}>
                {/* Thin cylinder for the coin shape */}
                <cylinderGeometry args={[2, 2, 0.15, 64]} />
                <meshStandardMaterial
                    color="#b8860b" // Dark gold
                    metalness={0.9}
                    roughness={0.2}
                    emissive="#3d2b00"
                    emissiveIntensity={0.5}
                />

                {/* Visual "embossing" effect using a second, slightly smaller mesh with distortion */}
                <mesh position={[0, 0.08, 0]}>
                    <cylinderGeometry args={[1.6, 1.6, 0.01, 32]} />
                    <meshStandardMaterial
                        color="#daa520" // Goldenrod
                        metalness={1}
                        roughness={0.1}
                    />
                </mesh>
                <mesh position={[0, -0.08, 0]}>
                    <cylinderGeometry args={[1.6, 1.6, 0.01, 32]} />
                    <meshStandardMaterial
                        color="#daa520"
                        metalness={1}
                        roughness={0.1}
                    />
                </mesh>
            </mesh>
        </Float>
    )
}

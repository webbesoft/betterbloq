import { useId } from 'react';

function Block({
    x,
    y,
    width,
    depth,
    height,
    tone,
}: {
    x: number;
    y: number;
    width: number;
    depth: number;
    height: number;
    tone: 'clay' | 'stone' | 'sage';
}) {
    const colors = {
        clay: ['#db9877', '#b96646', '#994d35'],
        stone: ['#f3f0df', '#dedcc9', '#c8cab6'],
        sage: ['#aebda0', '#839879', '#657f61'],
    }[tone];
    const top = `${x},${y - height} ${x + width},${y + width / 2 - height} ${x + width - depth},${y + (width + depth) / 2 - height} ${x - depth},${y + depth / 2 - height}`;
    return (
        <g stroke="#59664c" strokeWidth="0.7" strokeLinejoin="round">
            <polygon
                points={`${x - depth},${y + depth / 2 - height} ${x + width - depth},${y + (width + depth) / 2 - height} ${x + width - depth},${y + (width + depth) / 2} ${x - depth},${y + depth / 2}`}
                fill={colors[1]}
            />
            <polygon
                points={`${x + width - depth},${y + (width + depth) / 2 - height} ${x + width},${y + width / 2 - height} ${x + width},${y + width / 2} ${x + width - depth},${y + (width + depth) / 2}`}
                fill={colors[2]}
            />
            <polygon points={top} fill={colors[0]} />
        </g>
    );
}

export default function BuildingIllustration() {
    const patternId = useId();
    return (
        <div className="relative isolate overflow-hidden rounded-[2rem] border border-[#d6ddca] bg-[#e9eddf] text-[#3c4c37]">
            <div className="flex items-center justify-between px-7 pt-7 text-[10px] font-semibold tracking-[0.16em] uppercase">
                <span>The foundation of better</span>
                <span className="rounded border border-[#bfcab2] px-2 py-1">BB / 01</span>
            </div>
            <svg
                viewBox="0 0 520 410"
                className="mx-auto w-full max-w-[540px]"
                role="img"
                aria-label="An architectural illustration of clay, stone, and sage building blocks coming together"
            >
                <defs>
                    <pattern id={patternId} width="30" height="30" patternUnits="userSpaceOnUse" patternTransform="matrix(1 .5 -1 .5 260 40)">
                        <path d="M30 0H0V30" fill="none" stroke="#c8d1bb" strokeWidth="0.8" />
                    </pattern>
                </defs>
                <rect x="0" y="85" width="520" height="300" fill={`url(#${patternId})`} />
                <ellipse cx="267" cy="301" rx="153" ry="43" fill="#718164" opacity="0.1" />
                <path d="M106 244 262 166 427 248 271 326Z" fill="#d4dcc8" stroke="#9fae91" />
                <Block x={222} y={209} width={87} depth={76} height={118} tone="stone" />
                <Block x={310} y={252} width={74} depth={76} height={70} tone="sage" />
                <Block x={146} y={247} width={87} depth={39} height={42} tone="clay" />
                <Block x={185} y={267} width={87} depth={39} height={42} tone="clay" />
                <Block x={224} y={286} width={87} depth={39} height={42} tone="clay" />
                <Block x={185} y={225} width={87} depth={39} height={42} tone="clay" />
                <Block x={224} y={244} width={87} depth={39} height={42} tone="clay" />
                <path d="M137 306 271 373 416 301M137 301v10m134 57v10m145-82v10" fill="none" stroke="#7c8e6d" strokeWidth="0.9" />
                <path d="M240 81V47h95M117 220H73v49M386 224h51v-48" fill="none" stroke="#89997a" strokeDasharray="3 4" />
                <circle cx="240" cy="81" r="3" fill="#b96646" />
                <circle cx="117" cy="220" r="3" fill="#b96646" />
                <circle cx="386" cy="224" r="3" fill="#b96646" />
                <text x="260" y="36" fontSize="9" letterSpacing="1.5" fill="#4e6143">
                    A SHARED FOUNDATION
                </text>
                <text x="40" y="289" fontSize="9" letterSpacing="1.2" fill="#4e6143">
                    MATERIALS
                </text>
                <text x="400" y="164" fontSize="9" letterSpacing="1.2" fill="#4e6143">
                    MOMENTUM
                </text>
            </svg>
            <div className="mx-7 flex items-center justify-between gap-4 border-t border-[#c7d0ba] py-6">
                <p className="text-sm leading-5 font-medium">
                    Individual projects.
                    <br />
                    Collective buying power.
                </p>
                <span className="flex -space-x-1.5" aria-hidden="true">
                    {['bg-[#b96646]', 'bg-[#d4c8a5]', 'bg-[#839879]'].map((color) => (
                        <span key={color} className={`size-7 rounded-full border-[3px] border-[#e9eddf] ${color}`} />
                    ))}
                </span>
            </div>
        </div>
    );
}

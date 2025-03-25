import { LucideIcon } from 'lucide-react';
import { Card, CardContent, CardHeader } from './ui/card';

interface OverviewInfoProps {
    title: string;
    icon: LucideIcon | null;
    maintext: string;
    subtext: string;
}

export default function OverviewInfo(props: OverviewInfoProps) {
    const { title, icon: Icon, maintext, subtext } = props;

    return (
        <Card className="h-full">
            <CardHeader>
                <div className="flex items-center justify-between">
                    <p className="text-sm font-light">{title}</p>

                    {props.icon && <Icon />}
                </div>
            </CardHeader>
            <CardContent>
                <div className="flex flex-col">
                    <p className="text-xl font-bold">{maintext}</p>
                    <span className="text-gray text-sm font-light">{subtext}</span>
                </div>
            </CardContent>
        </Card>
    );
}
